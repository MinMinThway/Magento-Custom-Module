<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MMT\HomeSlider\Controller\Adminhtml\HomeSlider;

use Magento\Framework\Exception\LocalizedException;
use MMT\HomeSlider\Model\Uploader;
use MMT\HomeSlider\Model\UploaderPool;
use MMT\HomeSlider\Helper\HomeSliderGenerate;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @var HomeSliderGenerate
     */
    protected $homeSliderGenerate;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        UploaderPool $uploaderPool,
        StoreManagerInterface $storeManager,
        HomeSliderGenerate $homeSliderGenerate,
        PageRepositoryInterface $pageRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder

    ) {
        $this->dataPersistor = $dataPersistor;
        $this->uploaderPool = $uploaderPool;
        $this->homeSliderGenerate = $homeSliderGenerate;
        $this->storeManager = $storeManager;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        if (isset($data['rule'])) {
            unset($data['rule']);
        }
        if ($data) {
            $id = $this->getRequest()->getParam('homeslider_id');

            $model = $this->_objectManager->create(\MMT\HomeSlider\Model\HomeSlider::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Homeslider no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $home_slider_image = $this->getUploader('home_slider_image')->uploadFileAndGetName('home_slider_image', $data);
            $data['home_slider_image'] = $home_slider_image;
            $home_slider_image_mobile = $this->getUploader('home_slider_image_mobile')->uploadFileAndGetName('home_slider_image_mobile', $data);
            $data['home_slider_image_mobile'] = $home_slider_image_mobile;

            $home_slider_image_mobile_app = $this->getUploader('home_slider_image_mobile_app')->uploadFileAndGetName('home_slider_image_mobile_app', $data);
            $data['home_slider_image_mobile_app'] = $home_slider_image_mobile_app;
            $banner_image = $this->getUploader('banner_image')->uploadFileAndGetName('banner_image', $data);
            $data['banner_image'] = $banner_image;
            $data['website_id'] = implode(',', $data['website_id']);

            $model->loadPost($data);
            try {
                $model->save();
                //generate HomeSlider at block
                $url = "#";
                $currentStore = $this->storeManager->getStore();
                $baseUrl = $currentStore->getBaseUrl();
                $customUrl = 'hm__000__' . $model->getHomesliderId();
                $identifier = $baseUrl . 'hm__000__' . $model->getHomesliderId();
                $page = $this->filterBuilder->setField('identifier')
                    ->setConditionType('eq')
                    ->setValue($customUrl)
                    ->create();
                $filterGroupList = $this->_filterGroupBuilder->addFilter($page)->create();

                $searchCriteria = $this->searchCriteriaBuilder
                    ->setFilterGroups([$filterGroupList])
                    ->create();
                $pages = $this->pageRepository->getList($searchCriteria)->getItems();
                if (count($pages) > 0) {

                    $url = $identifier;
                }
                if(!$model->getIsActive()){
                    $model->deletPage($customUrl);
                }
                $this->homeSliderGenerate->homeSliderGenerate($model->getHomesliderId(), 'save', $model->getCategoryId(), $model->getWebsiteId(), $url);

                $this->messageManager->addSuccessMessage(__('You saved the Homeslider.'));
                $this->dataPersistor->clear('mit_homeslider_homeslider');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['homeslider_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Homeslider.'));
            }

            $this->dataPersistor->set('mit_homeslider_homeslider', $data);
            return $resultRedirect->setPath('*/*/edit', ['homeslider_id' => $this->getRequest()->getParam('homeslider_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    protected function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }
}