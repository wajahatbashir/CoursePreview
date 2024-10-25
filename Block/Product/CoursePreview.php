<?php

namespace CI\CoursePreview\Block\Product;

use Magento\Catalog\Block\Product\View;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;
use CI\CoursePreview\Helper\Data as CoursePreviewHelper;

class CoursePreview extends Template
{
    protected $registry;
    protected $logger;
    protected $coursePreviewHelper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param CoursePreviewHelper $coursePreviewHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        LoggerInterface $logger,
        CoursePreviewHelper $coursePreviewHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->coursePreviewHelper = $coursePreviewHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get course previews from the product
     *
     * @return array|null
     */
    public function getCoursePreviews()
    {
        // Add log to check if the block is being called
        //$this->logger->info('Course Preview Block Called');

        // Check if the Course Preview module is enabled in the system configuration
        if (!$this->coursePreviewHelper->isModuleEnabled()) {
            $this->logger->info('Course Preview module is disabled');
            return null; // Return null if the module is disabled
        }

        $product = $this->getProduct();
        $coursePreviews = $product->getData('course_previews');

        // Log the retrieved course previews data from the product
        if ($coursePreviews) {
            //$this->logger->info('Course Previews Data: ' . json_encode($coursePreviews));
            return json_decode($coursePreviews, true);
        } else {
            $this->logger->info('No Course Previews Data found');
        }

        return null;
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getProduct()
    {
        // Add log to verify if the product is loaded
        //$this->logger->info('Fetching the Current Product');

        return $this->registry->registry('current_product');
    }
}
