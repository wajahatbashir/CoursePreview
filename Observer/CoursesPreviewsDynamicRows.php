<?php

namespace CI\CoursePreview\Observer;

use CI\CoursePreview\Ui\DataProvider\Product\Form\Modifier\DynamicRowAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class CoursesPreviewsDynamicRows implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Execute the observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getDataObject();
        $postData = $this->request->getPostValue('product', []);

        $coursePreviews = $postData[DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE] ?? null;

        // Log course preview data
        //$this->logger->info('Course Previews Data: ' . json_encode($coursePreviews));

        if ($coursePreviews) {
            $requiredParams = ['title', 'description', 'video_url', 'video_upload'];

            if (is_array($coursePreviews)) {
                $coursePreviews = $this->removeEmptyEntries($coursePreviews, $requiredParams);
                $product->setData(
                    DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE,
                    $this->serializer->serialize($coursePreviews)
                );
            }
        } else {
            $product->setData(DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE, null);
        }
    }

    /**
     * Remove empty arrays from the multi-dimensional array
     *
     * @param array $coursePreviews
     * @param array $requiredParams
     * @return array
     */
    private function removeEmptyEntries(array $coursePreviews, array $requiredParams): array
    {
        $requiredParams = array_flip($requiredParams);

        foreach ($coursePreviews as $key => $values) {
            $values = array_filter($values);
            if (count(array_intersect_key($values, $requiredParams)) !== count($requiredParams)) {
                unset($coursePreviews[$key]);
            }
        }

        return $coursePreviews;
    }
}
