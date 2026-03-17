<?php
/**
 * AI Product Description Generator.
 *
 * Builds the prompt from product data and calls the OpenAI client.
 * Implements ProductDescriptionGeneratorInterface (service contract).
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Model;

use Mnaeem\AiProductDescription\Api\ProductDescriptionGeneratorInterface;
use Mnaeem\AiProductDescription\Model\Exception\ApiException;
use Mnaeem\AiProductDescription\Model\Http\OpenAiClient;

class ProductDescriptionGenerator implements ProductDescriptionGeneratorInterface
{
    private const PLACEHOLDER_PRODUCT_NAME = '{{product_name}}';
    private const PLACEHOLDER_SHORT_DESCRIPTION = '{{short_description}}';
    private const PLACEHOLDER_ATTRIBUTES = '{{attributes}}';
    private const PLACEHOLDER_CATEGORY = '{{category}}';
    private const PLACEHOLDER_KEYWORDS = '{{keywords}}';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OpenAiClient
     */
    private $openAiClient;

    /**
     * @param Config $config
     * @param OpenAiClient $openAiClient
     */
    public function __construct(
        Config $config,
        OpenAiClient $openAiClient
    ) {
        $this->config = $config;
        $this->openAiClient = $openAiClient;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        string $productName,
        string $shortDescription,
        string $attributes,
        string $category,
        string $keywords,
        ?int $storeId = null
    ): string {
        if (!$this->config->isEnabled($storeId)) {
            throw new ApiException(__('AI Product Description module is disabled.'));
        }
        if ($this->config->getApiProvider($storeId) !== 'openai') {
            throw new ApiException(__('Only OpenAI provider is supported at the moment.'));
        }

        $prompt = $this->buildPrompt($productName, $shortDescription, $attributes, $category, $keywords, $storeId);
        return $this->openAiClient->complete($prompt, $storeId);
    }

    /**
     * Build prompt from template and product data.
     *
     * @param string $productName
     * @param string $shortDescription
     * @param string $attributes
     * @param string $category
     * @param string $keywords
     * @param int|null $storeId
     * @return string
     */
    private function buildPrompt(
        string $productName,
        string $shortDescription,
        string $attributes,
        string $category,
        string $keywords,
        ?int $storeId = null
    ): string {
        $template = $this->config->getPromptTemplate($storeId);
        $prompt = str_replace(
            [
                self::PLACEHOLDER_PRODUCT_NAME,
                self::PLACEHOLDER_SHORT_DESCRIPTION,
                self::PLACEHOLDER_ATTRIBUTES,
                self::PLACEHOLDER_CATEGORY,
                self::PLACEHOLDER_KEYWORDS,
            ],
            [
                $this->escapePlaceholder($productName),
                $this->escapePlaceholder($shortDescription),
                $this->escapePlaceholder($attributes),
                $this->escapePlaceholder($category),
                $this->escapePlaceholder($keywords),
            ],
            $template
        );
        return $prompt;
    }

    /**
     * Escape placeholder value (strip tags for safety).
     *
     * @param string $value
     * @return string
     */
    private function escapePlaceholder(string $value): string
    {
        if ($value === '') {
            return 'N/A';
        }
        return strip_tags($value);
    }
}
