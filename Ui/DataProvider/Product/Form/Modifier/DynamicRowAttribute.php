<?php

namespace CI\CoursePreview\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class DynamicRowAttribute extends AbstractModifier
{
    public const PRODUCT_ATTRIBUTE_CODE = 'course_previews';
    public const FIELD_IS_DELETE = 'is_delete';
    public const FIELD_SORT_ORDER_NAME = 'sort_order';

    private $locator;
    private $attributeSetCollection;
    private $serializer;
    private $arrayManager;
    private $urlBuilder; // Add this
    private $logger;

    /**
     * Dependency Initialization
     *
     * @param LocatorInterface $locator
     * @param AttributeSetCollection $attributeSetCollection
     * @param SerializerInterface $serializer
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder // Add this
     * @param LoggerInterface $logger
     */
    public function __construct(
        LocatorInterface $locator,
        AttributeSetCollection $attributeSetCollection,
        SerializerInterface $serializer,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->locator = $locator;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->serializer = $serializer;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder; // Assign it here
        $this->logger = $logger;
    }

    /**
     * Modify Data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::PRODUCT_ATTRIBUTE_CODE;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        //$this->logger->info('Product ID: ' . $modelId);

        $coursePreviewsData = $model->getData(self::PRODUCT_ATTRIBUTE_CODE);

        if ($coursePreviewsData) {
            //$this->logger->info('Course Previews Data from Product: ' . json_encode($coursePreviewsData));
            $coursePreviewsData = $this->serializer->unserialize($coursePreviewsData, true);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . $fieldCode;
            $data = $this->arrayManager->set($path, $data, $coursePreviewsData);
        } else {
            $this->logger->info('Course Previews Data is empty');
        }

        return $data;
    }

    /**
     * Modify Meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {    
        // Log the entire meta array before searching for the path
        //$this->logger->info('Full Meta Array Before Path Search: ' . json_encode($meta));
    
        // Find the path for course previews in the meta structure
        $coursePreviewsPath = $this->arrayManager->findPath(
            self::PRODUCT_ATTRIBUTE_CODE,
            $meta,
            null,
            'children'
        );
    
        if ($coursePreviewsPath) {

           //$this->logger->info('Course Previews Path Found: ' . $coursePreviewsPath);
    
            // Merge and update meta with the dynamic row field structure
            $meta = $this->arrayManager->merge(
                $coursePreviewsPath,
                $meta,
                $this->initDynamicRowFieldStructure($meta, $coursePreviewsPath)
            );
    
            // Set and remove original path if needed
            $meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($coursePreviewsPath, 0, -3)
                    . '/' . self::PRODUCT_ATTRIBUTE_CODE,
                $meta,
                $this->arrayManager->get($coursePreviewsPath, $meta)
            );
    
            $meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($coursePreviewsPath, 0, -2),
                $meta
            );
    
        } else {
            // Log that the path was not found and output the meta structure
            $this->logger->info('Course Previews Path Not Found. Full Meta: ' . json_encode($meta));
        }
    
        return $meta;
    }

    /**
     * Add dynamic rows for course previews
     *
     * @param int $sortOrder
     * @return array
     */
    protected function addDynamicRowConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Course Preview'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                        'dndConfig' => [ // Enable drag and drop
                            'enabled' => true
                        ],
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        'title' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Title'),
                                        'dataScope' => 'title',
                                        'sortOrder' => 10,
                                        'validation' => [
                                            'required-entry' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'description' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => \Magento\Ui\Component\Form\Element\Textarea::NAME, // Change to textarea
                                        'dataType' => Text::NAME,
                                        'label' => __('Description'),
                                        'dataScope' => 'description',
                                        'sortOrder' => 20,
                                    ],
                                ],
                            ],
                        ],
                        'video_url' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Input::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Video URL'),
                                        'dataScope' => 'video_url',
                                        'sortOrder' => 30,
                                    ],
                                ],
                            ],
                        ],                                               
                        'video_uploads' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'field',
                                        'formElement' => 'fileUploader',
                                        'dataType' => 'text',
                                        'label' => __('Media Upload'),
                                        'dataScope' => 'video_uploads',
                                        'uploaderConfig' => [
                                            'url' => $this->urlBuilder->getUrl('ci_coursepreview/media/upload'), // Ensure this points to your custom upload controller
                                        ],
                                        'allowedExtensions' => ['mp4', 'avi', 'mkv', 'jpg', 'jpeg', 'png', 'gif'], // Supporting both videos and images
                                        'maxFileSize' => 1024 * 1000 * 1000, // 1GB max file size for large video uploads
                                        'sortOrder' => 40,
                                    ],
                                ],
                            ],
                        ],
                        'status' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'select', // This is the dropdown element type
                                        'dataType' => Text::NAME,
                                        'label' => __('Status'),
                                        'dataScope' => 'status',
                                        'sortOrder' => 50, // Sort order relative to other fields
                                        'options' => [
                                            [
                                                'label' => __('Enable'),
                                                'value' => '1'  // Enable value
                                            ],
                                            [
                                                'label' => __('Disable'),
                                                'value' => '0'  // Disable value
                                            ]
                                        ],
                                        'default' => '1', // Default value to be set as "Enable"
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                        'sortOrder' => 60,
                                    ],
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Initialize dynamic row field structure
     *
     * @param array $meta
     * @param string $coursePreviewsPath
     * @return array
     */
    protected function initDynamicRowFieldStructure($meta, $coursePreviewsPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Course Previews'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [ // Enable drag and drop
                            'enabled' => true
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get($coursePreviewsPath . '/arguments/data/config/sortOrder', $meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'title' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Title'),
                                        'dataScope' => 'title',
                                    ],
                                ],
                            ],
                        ],
                        'description' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => \Magento\Ui\Component\Form\Element\Textarea::NAME, // Change to textarea
                                        'dataType' => Text::NAME,
                                        'label' => __('Description'),
                                        'dataScope' => 'description',
                                    ],
                                ],
                            ],
                        ],
                        'video_url' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Video URL'),
                                        'dataScope' => 'video_url',
                                    ],
                                ],
                            ],
                        ],                        
                        'video_uploads' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'field',
                                        'formElement' => 'fileUploader',
                                        'dataType' => 'text',
                                        'label' => __('Media Upload'),
                                        'dataScope' => 'video_uploads',
                                        'uploaderConfig' => [
                                            'url' => $this->urlBuilder->getUrl('ci_coursepreview/media/upload'), // Ensure this points to your custom upload controller
                                        ],
                                        'allowedExtensions' => ['mp4', 'avi', 'mkv', 'jpg', 'jpeg', 'png', 'gif'], // Supporting both videos and images
                                        'maxFileSize' => 1024 * 1000 * 1000, // 1GB max file size for large video uploads
                                    ],
                                ],
                            ],
                        ],
                        'status' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => 'select', // This is the dropdown element type
                                        'dataType' => Text::NAME,
                                        'label' => __('Status'),
                                        'dataScope' => 'status',
                                        'options' => [
                                            [
                                                'label' => __('Enable'),
                                                'value' => '1'  // Enable value
                                            ],
                                            [
                                                'label' => __('Disable'),
                                                'value' => '0'  // Disable value
                                            ]
                                        ],
                                        'default' => '1', // Default value to be set as "Enable"
                                    ],
                                ],
                            ],
                        ],                        
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];
    }
}
