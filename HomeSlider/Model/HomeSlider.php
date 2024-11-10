<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MMT\HomeSlider\Model;

use Codeception\Step\Retry;
use MMT\HomeSlider\Api\Data\HomeSliderInterface;
use PhpParser\Node\Stmt\Return_;
use MMT\HomeSlider\Model\UploaderPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\AbstractModel;
use Magento\Catalog\Model\ProductFactory;
// use MMT\Discount\Helper\PromotionPageHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\PageFactory;

class HomeSlider extends AbstractModel implements HomeSliderInterface
{
    protected $_eventPrefix = 'mit_homeslider_homeslider';
    protected $_eventObject = 'homeslider';
    protected $condCombineFactory;
    protected $condProdCombineF;
    protected $validatedAddresses = [];
    protected $_selectProductIds;
    protected $_displayProductIds;
    private $localeDate;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    // /**
    //  * @var PromotionPageHelper
    //  */
    // protected $promotionPageHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializerInterface;




    /**
     * Sliders constructor.
     * @param Context $context
     * @param Registry $registry
     * @param UploaderPool $uploaderPool
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UploaderPool $uploaderPool,
        ProductFactory $productFactory,
        // PromotionPageHelper $promotionPageHelper,
        StoreManagerInterface $storeManager,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepository $pageRepository,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        PageFactory $pageFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->uploaderPool = $uploaderPool;
        // $this->promotionPageHelper = $promotionPageHelper;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->pageRepository = $pageRepository;
        $this->serializerInterface = $serializerInterface;
        $this->pageFactory = $pageFactory;
        $this->localeDate = $localeDate;
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;

    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init(\MMT\HomeSlider\Model\ResourceModel\HomeSlider::class);
    }

    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    public function getActionsInstance()
    {
        return $this->condCombineFactory->create();
    }

    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }

    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }

    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'homeslider_conditions_fieldset_' . $this->getId();
    }

    public function getActionFieldSetId($formName = '')
    {
        return $formName . 'homeslider_actions_fieldset_' . $this->getId();
    }

    public function getMatchProductIds()
    {
        $productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ProductFactory'
        );
        $this->_selectProductIds = [];
        $this->setCollectedAttributes([]);
        $this->getConditions()->collectValidatedAttributes($productCollection);
        \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Model\ResourceModel\Iterator'
        )->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProductCondition']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $productFactory->create(),
                ]
            );
        return $this->_selectProductIds;
    }

    public function callbackValidateProductCondition($args)
    {

        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        $ruleId = $this->getHomesliderId();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
        }
        $this->_selectProductIds[$product->getId()] = $results;
    }

    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Store\Model\StoreManagerInterface'
        )->getWebsites();
        foreach ($websites as $website) {
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }
    public function afterSave()
    {
        $this->generateCustomHomeSlider();
        return parent::afterSave();
    }

    public function afterDelete()
    {
        $identifier = 'hm__000__' . $this->getHomesliderId();
        $this->deletPage($identifier);
        return parent::afterDelete();
    }

    public function generateCustomHomeSlider()
    {
        $conditions = $this->getRuleConditionUnserialized($this->getConditionsSerialized());
        if ($conditions != null && count($conditions) > 0 && $this->getCategoryId() <= 0) {
            $this->getMatchProductIds();
            if (!empty($this->_selectProductIds) && is_array($this->_selectProductIds)) {
                $count = 1;
                foreach (explode(',', $this->getWebsiteId()) as $websiteId) {
                    $rows = [];
                    foreach ($this->_selectProductIds as $productId => $validationByWebsite) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }

                        $count++;
                        $rows[] = [
                            'product_id' => $productId,
                        ];


                    }
                }

                $promotionProductList = [];
                $skus = [];
                foreach ($rows as $id) {
                    $product = $this->productFactory->create()->load($id);
                    $skus[] = $product->getSku();
                }

                $promotionProductList[] = ['title' => '', 'product_skus' => implode(',', $skus), 'sort_order' => ''];
            }
            $identifier = 'hm__000__' . $this->getHomesliderId();
            if (count($promotionProductList) > 0) {
                //$promotionProductList = $this->promotionPageHelper->generateParentSkus($promotionProductList);

                $filteredId = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField('identifier')
                    ->setValue(($identifier))
                    ->create();
                $filterGroupList = [];
                $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
                $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
                $storeId = $this->storeManager->getStore()->getId();
                if ($result->getTotalCount() > 0) {
                    foreach ($result->getItems() as $item) {
                        // $item->setContent($this->promotionPageHelper->generatePageContent($promotionProductList, $this->getBannerImageUrl(), 2))
                        //     ->setTitle($this->getPercentageName())
                        //     ->setIdentifier($identifier)
                        //     ->setIsActive(true)
                        //     ->setStores(array($storeId))
                        //     ->setPageLayout('cms-full-width');
                        // $this->pageRepository->save($item);
                        return true;
                    }
                } else {
                    // $page = $this->pageFactory->create();
                    // $page->setTitle($this->getPercentageName())
                    //     ->setIdentifier($identifier)
                    //     ->setIsActive(true)
                    //     ->setPageLayout('cms-full-width')
                    //     ->setStores(array($storeId))
                    //     ->setContent($this->promotionPageHelper->generatePageContent($promotionProductList, $this->getBannerImageUrl(), 2));
                    // $this->pageRepository->save($page);
                    return true;
                }
            } else {
                // $filteredId = $this->_filterBuilder
                //     ->setConditionType('eq')
                //     ->setField('identifier')
                //     ->setValue(($identifier))
                //     ->create();
                // $filterGroupList = [];
                // $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                // $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
                // $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
                // foreach ($result->getItems() as $item) {
                //     $this->pageRepository->deleteById($item->getId());
                // }
            }
        } else {
            // $identifier = 'hm__000__' . $this->getHomesliderId();
            // $this->deletPage($identifier);
        }
    }

    /**
     * @inheritDoc
     */
    public function setHomesliderId($homesliderId)
    {
        return $this->setData(self::HOMESLIDER_ID, $homesliderId);
    }

    /**
     * @inheritDoc
     */
    public function getHomesliderId()
    {
        return $this->getData(self::HOMESLIDER_ID);
    }


    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }



    /**
     * @inheritDoc
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setHomeSliderImage($image)
    {
        return $this->setData(self::HOME_SLIDER_IMAGE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getHomeSliderImage()
    {
        return $this->getData(self::HOME_SLIDER_IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setHomeSliderImageMobile($image)
    {
        return $this->setData(self::HOME_SLIDER_IMAGE_MOBILE, $image);
    }

    /**
     * @inheritDoc
     */
    public function getHomeSliderImageMobile()
    {
        return $this->getData(self::HOME_SLIDER_IMAGE_MOBILE);
    }

    /**
     * @inheritDoc
     */
    public function setHomeSliderImageMobileApp($image){
        return $this->setData(self::HOME_SLIDER_IMAGE_MOBILE_APP,$image);
    }

    /**
     * @inheritDoc
     */
    public function getHomeSliderImageMobileApp(){
        return $this->getData(self::HOME_SLIDER_IMAGE_MOBILE_APP);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIsHomeSlider($isHomeSlider)
    {
        return $this->setData(self::IS_HOME_SLIDER, $isHomeSlider);
    }

    /**
     * @inheritDoc
     */
    public function getIsHomeSlider()
    {
        return $this->getData(self::IS_HOME_SLIDER);
    }
    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }


    /**
     * @inheritDoc
     */
    public function setIsHomeSliderOne($isHomeSliderOne)
    {
        return $this->setData(self::IS_HOME_SLIDER_ONE, $isHomeSliderOne);
    }

    /**
     * @inheritDoc
     */
    public function getIsHomeSliderOne()
    {
        return $this->getData(self::IS_HOME_SLIDER_ONE);
    }

    /**
     * @inheirtDoc
     */
    public function setPercentageName($percentageName)
    {
        return $this->setData(self::PERCENTAGE_NAME, $percentageName);
    }

    /**
     * @inheirtDoc
     */
    public function getPercentageName()
    {
        return $this->getData(self::PERCENTAGE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * @inheritDoc
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheirtDoc
     */
    public function setBannerImage(string $banner_image)
    {
        return $this->setData(self::BANNER_IMAGE, $banner_image);
    }

    /**
     * @inheirtDoc
     */
    public function getBannerImage()
    {
        return $this->getData(self::BANNER_IMAGE);
    }

    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getHomeSliderImageUrl()
    {
        $url = false;
        $image = $this->getHomeSliderImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('home_slider_image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }


    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getHomeSliderImageMobileUrl()
    {
        $url = false;
        $image = $this->getHomeSliderImageMobile();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('home_slider_image_mobile');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getHomeSliderImageMobilAppeUrl()
    {
        $url = false;
        $image = $this->getHomeSliderImageMobileApp();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('home_slider_image_mobile_app');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * Get image URL
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getBannerImageUrl()
    {
        $url = false;
        $image = $this->getBannerImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('banner_image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * unserialized conditons
     * @param string $serializedData
     * @return array
     */
    public function getRuleConditionUnserialized($serializedData)
    {
        $data = $this->serializerInterface->unserialize($serializedData);
        if (isset($data['conditions'])) {
            return $data['conditions'];
        }
        return [];
    }

    /**
     * delete page function
     *
     * @param string $identifier
     * @return void
     */
    public function deletPage($identifier)
    {

        $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue(($identifier))
            ->create();
        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
        $this->searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $result = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($result->getItems() as $item) {

            $this->pageRepository->deleteById($item->getId());
        }
    }
}
