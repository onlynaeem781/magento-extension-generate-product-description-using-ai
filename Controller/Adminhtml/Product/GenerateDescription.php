<?php
/**
 * Admin controller: Generate AI product description via AJAX.
 *
 * Receives product id, loads product data, calls AI service, returns JSON
 * with generated description or error message.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Mnaeem\AiProductDescription\Api\ProductDescriptionGeneratorInterface;
use Mnaeem\AiProductDescription\Model\Exception\ApiException;
use Psr\Log\LoggerInterface;

class GenerateDescription extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level (same as catalog product edit).
     *
     * @see _isAllowed
     */
    public const ADMIN_RESOURCE = 'Mnaeem_AiProductDescription::generate';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductDescriptionGeneratorInterface
     */
    private $descriptionGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductDescriptionGeneratorInterface $descriptionGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductDescriptionGeneratorInterface $descriptionGenerator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->descriptionGenerator = $descriptionGenerator;
        $this->logger = $logger;
    }

    /**
     * Execute: load product, build context, generate description, return JSON.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $productId = (int) $this->getRequest()->getPost('product_id');
        if ($productId <= 0) {
            $result->setData(['success' => false, 'message' => __('Invalid product.')]);
            return $result;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $result->setData(['success' => false, 'message' => __('Product not found.')]);
            return $result;
        }

        $storeId = (int) $this->getRequest()->getPost('store_id');
        if ($storeId > 0) {
            $product->setStoreId($storeId);
        }

        $productName = (string) $product->getName();
        $shortDescription = (string) $product->getData('short_description');
        $attributes = $this->getAttributesText($product);
        $category = $this->getCategoryNames($product);
        $keywords = (string) ($product->getData('meta_keyword') ?? '');

        try {
            $description = $this->descriptionGenerator->generate(
                $productName,
                $shortDescription,
                $attributes,
                $category,
                $keywords,
                $storeId > 0 ? $storeId : null
            );
            $result->setData([
                'success' => true,
                'description' => $description,
            ]);
        } catch (ApiException $e) {
            $this->logger->warning('AI Product Description API error: ' . $e->getMessage());
            $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('AI Product Description error: ' . $e->getMessage(), ['exception' => $e]);
            $result->setData([
                'success' => false,
                'message' => __('An error occurred while generating the description. Please try again.'),
            ]);
        }

        return $result;
    }

    /**
     * Get product attributes as a text string (excluding some system attributes).
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    private function getAttributesText($product): string
    {
        $skip = [
            'name', 'sku', 'price', 'short_description', 'description',
            'meta_title', 'meta_keyword', 'meta_description', 'tier_price',
            'quantity_and_stock_status', 'weight', 'url_key', 'visibility',
            'status', 'created_at', 'updated_at',
        ];
        $parts = [];
        foreach ($product->getData() as $code => $value) {
            if (in_array($code, $skip, true)) {
                continue;
            }
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $parts[] = $code . ': ' . $value;
        }
        return implode("\n", $parts);
    }

    /**
     * Get category names for the product.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    private function getCategoryNames($product): string
    {
        $categoryIds = $product->getCategoryIds();
        if (!is_array($categoryIds) || empty($categoryIds)) {
            return '';
        }
        $names = [];
        foreach (array_slice($categoryIds, 0, 5) as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId, $product->getStoreId());
                $names[] = $category->getName();
            } catch (\Exception $e) {
                continue;
            }
        }
        return implode(', ', $names);
    }
}
