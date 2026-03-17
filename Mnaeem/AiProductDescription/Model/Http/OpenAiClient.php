<?php
/**
 * HTTP client for OpenAI API.
 *
 * Sends chat completion requests and returns the generated text.
 * Handles errors and timeouts.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Model\Http;

use Mnaeem\AiProductDescription\Model\Config;
use Mnaeem\AiProductDescription\Model\Exception\ApiException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;

class OpenAiClient
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param Config $config
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $config
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->config = $config;
    }

    /**
     * Send prompt to OpenAI and return generated text.
     *
     * @param string $prompt User message / prompt
     * @param int|null $storeId
     * @return string Generated content
     * @throws ApiException
     */
    public function complete(string $prompt, ?int $storeId = null): string
    {
        $apiKey = $this->config->getApiKey($storeId);
        if ($apiKey === '') {
            throw new ApiException(__('API Key is not configured. Please set it in Stores → Configuration → AI Product Description.'));
        }

        $payload = [
            'model' => $this->config->getApiModel($storeId),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $this->config->getMaxTokens($storeId),
            'temperature' => $this->config->getTemperature($storeId),
        ];

        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Authorization', 'Bearer ' . $apiKey);
        $this->curl->post(self::OPENAI_API_URL, $this->json->serialize($payload));

        $body = $this->curl->getBody();
        $status = $this->curl->getStatus();

        if ($status >= 400) {
            $this->handleError($body, $status);
        }

        $data = $this->decodeResponse($body);
        return $this->extractContent($data);
    }

    /**
     * Decode JSON response and validate structure.
     *
     * @param string $body
     * @return array
     * @throws ApiException
     */
    private function decodeResponse(string $body): array
    {
        try {
            $data = $this->json->unserialize($body);
        } catch (\InvalidArgumentException $e) {
            throw new ApiException(__('Invalid API response: %1', $e->getMessage()));
        }
        if (!is_array($data)) {
            throw new ApiException(__('Invalid API response format.'));
        }
        return $data;
    }

    /**
     * Extract content from OpenAI chat completion response.
     *
     * @param array $data
     * @return string
     * @throws ApiException
     */
    private function extractContent(array $data): string
    {
        $choices = $data['choices'] ?? null;
        if (!is_array($choices) || empty($choices)) {
            throw new ApiException(__('API returned no choices.'));
        }
        $first = $choices[0];
        $message = $first['message'] ?? null;
        if (!is_array($message)) {
            throw new ApiException(__('API response missing message.'));
        }
        $content = $message['content'] ?? null;
        if ($content === null) {
            throw new ApiException(__('API returned empty content.'));
        }
        return trim((string) $content);
    }

    /**
     * Handle API error response.
     *
     * @param string $body
     * @param int $status
     * @return void
     * @throws ApiException
     */
    private function handleError(string $body, int $status): void
    {
        $message = __('AI API request failed with status %1.', $status);
        try {
            $data = $this->json->unserialize($body);
            if (is_array($data)) {
                $error = $data['error'] ?? [];
                if (is_array($error) && isset($error['message'])) {
                    $apiMessage = (string) $error['message'];
                    $message = __('AI API error: %1', $apiMessage);
                    // Add actionable guidance for quota/billing errors from OpenAI
                    if (stripos($apiMessage, 'quota') !== false || stripos($apiMessage, 'billing') !== false) {
                        $message = __(
                            'OpenAI reported a quota or billing limit. Please check: (1) Your API key is for the correct account with credits. (2) Billing is set up at platform.openai.com/account/billing (3) You have not hit rate or usage limits. Original error: %1',
                            $apiMessage
                        );
                    }
                }
            }
        } catch (\InvalidArgumentException $e) {
            // keep default message
        }
        throw new ApiException($message);
    }
}
