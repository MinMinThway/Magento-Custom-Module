<?php

namespace MMT\Product\Model\Api;

use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteRepository;
use MMT\Product\Api\WebsiteInterface;
use MMT\Product\Model\WebsiteDataFactory;
use Magento\Customer\Model\Config\Share as ShareConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Website implements WebsiteInterface
{
    /**
     * @var WebsiteRepository
     */
    private $websiteRepository;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface 
     */
    private $storeManagerInterface;

    /**
     * @var WebsiteDataFactory
     */
    private $websiteDataFactory;

    /**
     * @var ShareConfig
     */
    private $shareConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    public function __construct(
        WebsiteRepository $websiteRepository,
        GroupRepository $groupRepository,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManagerInterface,
        WebsiteDataFactory $websiteDataFactory,
        ShareConfig $shareConfig,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->collectionFactory = $collectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->websiteDataFactory = $websiteDataFactory;
        $this->shareConfig = $shareConfig;
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * @inheritdoc
     */
    public function getWebsites()
    {
        $result = [];
        $websiteData = $this->websiteDataFactory->create();
        $websiteData->setIsAccountShare($this->shareConfig->isGlobalScope());
        $websiteList = $this->websiteRepository->getList();
        foreach ($websiteList as $website) {
            if ($website->getId()) {
                $web = [];
                $web['id'] = $website->getId();
                $web['code'] = $website->getCode();
                $web['name'] = $website->getName();
                $web['default_group_id'] = $website->getDefaultGroupId();
                $groupData = $this->groupRepository->get($website->getId());
                $group = [];
                $group['id'] = $groupData->getId();
                $group['website_id'] = $groupData->getWebsiteId();
                $group['root_category_id'] = $groupData->getRootCategoryId();
                $group['default_store_id'] = $groupData->getDefaultStoreId();
                $group['name'] = $groupData->getName();
                $group['code'] = $groupData->getCode();
    
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('website_id', $groupData->getWebsiteId())
                    ->addFieldToFilter('group_id', $groupData->getId())
                    ->addFieldToFilter('is_active', 1);
    
                $store = [];
                foreach ($collection as $data) {
                    $storeData['id'] = $data->getId();
                    $storeData['code'] = $data->getCode();
                    $storeData['name'] = $data->getName();
                    $store[] = $storeData;
                    $web['url'] = $this->storeManagerInterface->getStore($data->getId())->getBaseUrl();
                    $web['image'] = $this->storeManagerInterface->getStore($data->getId())->getBaseUrl() . 'media/websites_images/' . 
                    $this->scopeConfigInterface->getValue('web/image_options/image', ScopeInterface::SCOPE_WEBSITE, $website->getId());
                }
                $group['stores'] = $store;
                $web['group'] = $group;
                $result[] = $web;
            }
        }
        $websiteData->setWebsites([$result]);
        return $websiteData;
    }
}
