<?php
/**
 * Service contract for AI product description generation.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Api;

/**
 * Interface ProductDescriptionGeneratorInterface
 *
 * Responsible for sending product context to the AI API and returning
 * generated SEO-optimized description text.
 */
interface ProductDescriptionGeneratorInterface
{
    /**
     * Generate product description using AI from the given product data.
     *
     * @param string $productName Product name
     * @param string $shortDescription Short description (can be empty)
     * @param string $attributes Attributes text (e.g. key: value list)
     * @param string $category Category name(s)
     * @param string $keywords Comma-separated or space-separated keywords
     * @param int|null $storeId Store ID for config scope (optional)
     * @return string Generated description text
     * @throws \Mnaeem\AiProductDescription\Model\Exception\ApiException
     */
    public function generate(
        string $productName,
        string $shortDescription,
        string $attributes,
        string $category,
        string $keywords,
        ?int $storeId = null
    ): string;
}
