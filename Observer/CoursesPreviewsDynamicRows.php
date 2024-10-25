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
    protected $request;
    protected $serializer;
    protected $logger;

    public function __construct(
        RequestInterface $request,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getDataObject();
        $postData = $this->request->getPostValue('product', []);

        // Log the incoming post data for debugging
        //$this->logger->info('Post Data: ' . json_encode($postData));

        $coursePreviews = $postData[DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE] ?? null;

        // Log course preview data
        //$this->logger->info('Course Previews Data: ' . json_encode($coursePreviews));

        if ($coursePreviews) {
            $requiredParams = ['title']; // Remove 'description' from requiredParams

            if (is_array($coursePreviews)) {
                // Adjusted the filtering logic to allow empty description but ensure video_url or video_uploads must exist
                $coursePreviews = $this->removeEmptyEntries($coursePreviews, $requiredParams);

                // Log the filtered data to check if entries are being removed correctly
                //$this->logger->info('Filtered Course Previews: ' . json_encode($coursePreviews));

                if (!empty($coursePreviews)) {
                    $product->setData(
                        DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE,
                        $this->serializer->serialize($coursePreviews)
                    );
                } else {
                    $this->logger->info('No valid Course Previews left after filtering.');
                    $product->setData(DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE, null);
                }
            }
        } else {
            $this->logger->info('No Course Previews found in post data.');
            $product->setData(DynamicRowAttribute::PRODUCT_ATTRIBUTE_CODE, null);
        }
    }

    private function removeEmptyEntries(array $coursePreviews, array $requiredParams): array
    {
        $requiredParams = array_flip($requiredParams);

        foreach ($coursePreviews as $key => $values) {
            $values = array_filter($values);

            // Ensure either video_url or video_uploads must exist along with title (description can be empty)
            $hasVideoUrl = !empty($values['video_url']);
            $hasVideoUploads = !empty($values['video_uploads']);

            // Only remove the entry if title is missing or neither video_url nor video_uploads are provided
            if (count(array_intersect_key($values, $requiredParams)) !== count($requiredParams) || (!$hasVideoUrl && !$hasVideoUploads)) {
                $this->logger->info('Removing incomplete Course Preview entry: ' . json_encode($values));
                unset($coursePreviews[$key]);
            }
        }

        return $coursePreviews;
    }
}
