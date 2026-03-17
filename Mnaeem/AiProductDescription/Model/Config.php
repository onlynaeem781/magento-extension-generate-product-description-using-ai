<?php
/**
 * Module configuration model.
 *
 * Reads settings from Stores → Configuration → AI Product Description.
 * API key is stored securely via Magento's encrypted config.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'mnaeem_aiproductdescription/general/enabled';
    private const XML_PATH_API_PROVIDER = 'mnaeem_aiproductdescription/general/api_provider';
    private const XML_PATH_API_KEY = 'mnaeem_aiproductdescription/general/api_key';
    private const XML_PATH_API_MODEL = 'mnaeem_aiproductdescription/general/api_model';
    private const XML_PATH_MAX_TOKENS = 'mnaeem_aiproductdescription/general/max_tokens';
    private const XML_PATH_TEMPERATURE = 'mnaeem_aiproductdescription/general/temperature';
    private const XML_PATH_PROMPT_TEMPLATE = 'mnaeem_aiproductdescription/prompt/template';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Whether the module is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * API provider code (e.g. openai).
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiProvider(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_API_PROVIDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * API key (stored encrypted when using env or encrypted config).
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiKey(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * AI model name (e.g. gpt-4o-mini).
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiModel(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_API_MODEL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Max tokens for API response.
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxTokens(?int $storeId = null): int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_TOKENS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return (int) ($value ?: 1000);
    }

    /**
     * Temperature for API (0–2).
     *
     * @param int|null $storeId
     * @return float
     */
    public function getTemperature(?int $storeId = null): float
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_TEMPERATURE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return (float) ($value !== null && $value !== '' ? $value : 0.7);
    }

    /**
     * Default prompt template with placeholders.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPromptTemplate(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_PROMPT_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
