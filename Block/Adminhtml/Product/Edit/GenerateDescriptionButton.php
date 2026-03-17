<?php
/**
 * Block for "Generate AI Description" button on product edit page.
 *
 * Provides URL, product id, store id and form key for the AJAX request.
 *
 * @category  Mnaeem
 * @package   Mnaeem_AiProductDescription
 */

declare(strict_types=1);

namespace Mnaeem\AiProductDescription\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\UrlInterface;

class GenerateDescriptionButton extends Template
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get URL for generate description action.
     *
     * @return string
     */
    public function getGenerateUrl(): string
    {
        return $this->urlBuilder->getUrl('mnaeem_aiproductdescription/product/generateDescription');
    }

    /**
     * Get current product id from request (product edit page).
     *
     * @return int
     */
    public function getProductId(): int
    {
        return (int) $this->getRequest()->getParam('id', 0);
    }

    /**
     * Get store id from request for scope.
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->getRequest()->getParam('store', 0);
    }

}
