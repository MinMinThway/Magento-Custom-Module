<?php

declare(strict_types=1);

namespace MMT\HomeSlider\Controller\Adminhtml\HomeSlider;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
// use MMT\CatalogRule\Model\ImageUploader;
use MMT\HomeSlider\Model\Uploader;

/**
 * Class Upload
 */
class Upload extends Action
{
    /**
     * @var string
     */
    const ACTION_RESOURCE = 'MMT_HomeSlider::top_level';

    /**
     * @var Uploader
     */
    protected $uploader;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param Uploader $uploader
     */
    public function __construct(
        Context $context,
        Uploader $uploader
    ) {
        parent::__construct($context);
        $this->uploader = $uploader;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->uploader->saveFileToTmpDir($this->getFieldName());

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @return string
     */
    protected function getFieldName()
    {

        return $this->_request->getParam('field');
    }
}