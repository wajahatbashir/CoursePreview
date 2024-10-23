<?php
namespace CI\CoursePreview\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_COURSE_PREVIEW_ENABLE = 'coursepreview/general/enable';

    /**
     * Check if the Course Preview module is enabled
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isModuleEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_COURSE_PREVIEW_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
