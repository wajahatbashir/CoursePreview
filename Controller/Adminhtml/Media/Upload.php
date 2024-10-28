<?php
namespace CI\CoursePreview\Controller\Adminhtml\Media;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

class Upload extends Action
{
    protected $jsonFactory;
    protected $uploaderFactory;
    protected $mediaDirectory;
    protected $logger;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            // Log the entire $_FILES array for debugging
           // $this->logger->info('FILES Array: ' . print_r($_FILES, true));

            // Check if 'course_previews' exists in the 'product' array of $_FILES
            if (isset($_FILES['product']['name']['course_previews'])) {
                foreach ($_FILES['product']['name']['course_previews'] as $index => $fileInfo) {
                    if (!empty($_FILES['product']['tmp_name']['course_previews'][$index]['video_uploads'])) {

                        // Rebuild the file array structure
                        $fileArray = [
                            'name' => $_FILES['product']['name']['course_previews'][$index]['video_uploads'],
                            'type' => $_FILES['product']['type']['course_previews'][$index]['video_uploads'],
                            'tmp_name' => $_FILES['product']['tmp_name']['course_previews'][$index]['video_uploads'],
                            'error' => $_FILES['product']['error']['course_previews'][$index]['video_uploads'],
                            'size' => $_FILES['product']['size']['course_previews'][$index]['video_uploads']
                        ];

                        // Log the new file array for debugging
                        //$this->logger->info('File Array for Upload: ' . print_r($fileArray, true));

                        // Manually handle the file upload
                        $uploadDir = $this->mediaDirectory->getAbsolutePath('course_videos');
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true); // Ensure the directory exists
                        }

                        $fileName = basename($fileArray['name']);
                        $destination = $uploadDir . '/' . $fileName;

                        // Move the uploaded file to the destination
                        if (move_uploaded_file($fileArray['tmp_name'], $destination)) {
                           // $this->logger->info('File uploaded successfully to: ' . $destination);

                            // Return success response with file URL and name
                            return $this->jsonFactory->create()->setData([
                                'name' => $fileName,
                                'url' => $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'course_videos/' . $fileName,
                                'size' => $fileArray['size']
                            ]);
                        } else {
                            throw new \Exception('Failed to move uploaded file.');
                        }
                    }
                }

                throw new \Exception("No file found for upload in the course previews.");
            } else {
                throw new \Exception("No file found for upload.");
            }
        } catch (\Exception $e) {
            // Log the error message
            $this->logger->error('Error during file upload: ' . $e->getMessage());

            return $this->jsonFactory->create()->setData([
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ]);
        }
    }


}
