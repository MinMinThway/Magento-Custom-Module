<?php

namespace MMT\Product\Model\Api;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use MMT\Product\Api\ProductApiInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
// use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;
// use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
// use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
// use MMT\WeeklyPromo\Helper\PromoRetriever;
use MMT\Product\Model\Api\CustomLayerNavigationFactory;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeListFactory;
use MMT\Product\Model\Layer\FilterListFactory;
use Magento\Catalog\Model\Layer\ResolverFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use MMT\Product\Model\ProductFactory;
use MMT\Product\Model\ProductResultListFactory;
use Magento\Catalog\Model\CategoryFactory;
// use Mageplaza\RewardPoints\Helper\Point as PointHelper;

class ProductApi implements ProductApiInterface
{

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    // /**
    //  * @var CatalogRuleFactory
    //  */
    // private $catalogRuleFactory;

    // /**
    //  * @var BehaviorFactory
    //  */
    // private $behaviorFactory;

    // /**
    //  * @var PromoRetriever
    //  */
    // private $promoRetriever;

    /**
     * @var FilterableAttributeListFactory
     */
    private $filterableAttributeListFactory;

    /**
     * @var CustomLayerNavigationFactory
     */
    private $customLayerNavigationFactory;

    /**
     * @var FilterListFactory
     */
    private $filterListFactory;

    /**
     * @var ResolverFactory
     */
    private $resolverFactory;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var ProductResultListFactory
     */
    private $productResultListFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CategoryFactory;
     */
    private $categoryFactory;

    // /**
    //  * @var PointHelper
    //  */
    // private $pointHelper;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        // CatalogRuleFactory $catalogRuleFactory,
        // BehaviorFactory $behaviorFactory,
        // PromoRetriever $promoRetriever,
        FilterableAttributeListFactory $filterableAttributeListFactory,
        CustomLayerNavigationFactory $customLayerNavigationFactory,
        FilterListFactory $filterListFactory,
        ResolverFactory $resolverFactory,
        RequestInterface $requestInterface,
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezoneInterface,
        CatalogConfig $catalogConfig,
        ProductResultListFactory $productResultListFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory
        // PointHelper $pointHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        // $this->catalogRuleFactory = $catalogRuleFactory;
        // $this->behaviorFactory = $behaviorFactory;
        // $this->promoRetriever = $promoRetriever;
        $this->filterableAttributeListFactory = $filterableAttributeListFactory;
        $this->customLayerNavigationFactory = $customLayerNavigationFactory;
        $this->filterListFactory = $filterListFactory;
        $this->resolverFactory = $resolverFactory;
        $this->requestInterface = $requestInterface;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->timezoneInterface = $timezoneInterface;
        $this->catalogConfig = $catalogConfig;
        $this->productResultListFactory = $productResultListFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        // $this->pointHelper = $pointHelper;
    }

    public function getList($categoryId, SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '')
    {
        $collection = $this->productCollectionFactory->create();
        $scopeTz = new \DateTimeZone(
            $this->timezoneInterface->getConfigTimezone(ScopeInterface::SCOPE_WEBSITE, $this->storeManagerInterface->getStore()->getWebsiteId())
        );
        $date = (new \DateTime('now', $scopeTz))->getTimestamp();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('visibility', ['eq' => 4]);
        $collection->addFieldToFilter('status', ['eq' => 1]);
        $collection->joinField(
            'stock_status',
            'cataloginventory_stock_status',
            'stock_status',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        )->addFieldToFilter('stock_status', array('eq' => \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK));

        if ($customSort) {
            $collection->getSelect()->order($sortQuery);
        } else {
            // $collection->addAttributeToSort('position', 'ASC');
            if ($searchCriteria->getSortOrders() > 0) {
                foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                    if ($sortOrder->getField() === 'best_seller') {
                        $collection->getSelect()->joinLeft(
                            'sales_order_item',
                            'e.entity_id = sales_order_item.product_id',
                            array('qty_ordered' => 'SUM(sales_order_item.qty_ordered)')
                        )
                            ->group('e.entity_id')
                            ->order('qty_ordered ' . $sortOrder->getDirection());
                    } else {
                        $collection->addAttributeToSort($sortOrder->getField(), $sortOrder->getDirection());
                    }
                }
            } else {
                $key = $this->catalogConfig->getProductListDefaultSortBy($this->storeManagerInterface->getStore()->getId());
                if ($key == 'price_asc') {
                    $collection->addAttributeToSort('price', 'ASC');
                } else if ($key == 'price_desc') {
                    $collection->addAttributeToSort('price', 'DESC');
                } else {
                    if ($key === 'best_seller') {
                        $collection->getSelect()->joinLeft(
                            'sales_order_item',
                            'e.entity_id = sales_order_item.product_id',
                            array('qty_ordered' => 'SUM(sales_order_item.qty_ordered)')
                        )
                            ->group('e.entity_id')
                            ->order('qty_ordered ' . 'desc');
                    }
                    $collection->addAttributeToSort($key, 'DESC');
                }
            }
            $collection->addAttributeToSort('position', 'ASC');
        }
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        if ($categoryId > 0) {
            $category = $this->categoryFactory->create()->load($categoryId);
            $categoryIdList = $category->getAllChildren(false);
            $categoryIds = explode(',', $categoryIdList);
            $categoryIds[] = $categoryId;
            $collection->addCategoriesFilter(['in' => $categoryIds]);
            if (count($searchCriteria->getFilterGroups()) > 0) {
                $collection = $this->generateShoppingOptionFilter($categoryId, $collection, $searchCriteria);
            }
        } else if (count($searchCriteria->getFilterGroups()) > 0) {
            foreach ($searchCriteria->getFilterGroups() as $filterGroups) {
                foreach ($filterGroups->getFilters() as $filter) {
                    $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
                }
            }
        }
        $collection->addMediaGalleryData()
            ->addMinimalPrice()
            ->addFinalPrice();
        $collection->addPriceData();

        // $collection->getSelect()->joinLeft(
        //     'mit_discount_label',
        //     'e.entity_id = mit_discount_label.product_id and 
        // mit_discount_label.customer_group_id = 0 and (mit_discount_label.from_time = 0 or mit_discount_label.from_time < ' . $date . ') and
        // (mit_discount_label.to_time = 0 or mit_discount_label.to_time > ' . $date . ') ',
        //     array(
        //         'catalogrule_label' => 'discount_label',  'catalogrule_width' => 'width', 'catalogrule_height' => 'height',
        //         'catalogrule_sortorder' => 'sort_order',  'catalogrule_labelcolor' => 'discount_label_color',  'catalogrule_labelstyle' => 'discount_label_style'
        //     )
        // );

        // $collection->getSelect()->joinLeft(
        //     'mit_salesrule_label_product',
        //     'e.entity_id = mit_salesrule_label_product.product_id and
        // mit_salesrule_label_product.customer_group_id = 0 and (mit_salesrule_label_product.from_time = 0 or mit_salesrule_label_product.from_time < ' . $date . ') and
        // (mit_salesrule_label_product.to_time = 0 or mit_salesrule_label_product.to_time > ' . $date . ') ',
        //     array(
        //         'salesrule_label' => 'discount_label',  'salesrule_width' => 'width', 'salesrule_height' => 'height',
        //         'salesrule_sortorder' => 'sort_order', 'salesrule_labelcolor' => 'discount_label_color', 'salesrule_labelstyle' => 'discount_label_style'
        //     )
        // );

        //echo($collection->getSelect()->__toString());
        $result = [];

        // $pointReview = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::PRODUCT_REVIEW) ?: 0;
        // $freeImgPath = $this->promoRetriever->getFreeShippingImgPath();
        $productList = $this->productResultListFactory->create();

        $index = 0;
        foreach ($collection as $data) {
            $item = $this->productFactory->create();
            $check = false;
            $reward = 0;
            $pointReview = 0;
            // $check =  $this->promoRetriever->isFreeShipping([$data->getId()]);
            // $reward = $this->catalogRuleFactory->create()->getPointEarnFromRules($data);
            $regularPrice = $data->getPriceInfo()->getPrice('regular_price')->getAmount();
            $finalPrice   = $data->getPriceInfo()->getPrice('final_price')->getAmount();

            $discountLabel        = '';
            $type = 0;
            // if (isset($collection->getData()[$index]['salesrule_sortorder']) && isset($collection->getData()[$index]['catalogrule_sortorder'])) {
            //     $type = 1;
            //     if ($collection->getData()[$index]['salesrule_sortorder'] > $collection->getData()[$index]['catalogrule_sortorder']) {
            //         $type = 2;
            //     }
            // } else if ($collection->getData()[$index]['catalogrule_sortorder']) {
            //     $type = 1;
            // } else if ($collection->getData()[$index]['salesrule_sortorder']) {
            //     $type = 2;
            // } else if ($collection->getData()[$index]['catalogrule_label']) {
            //     $type = 1;
            // } else if ($collection->getData()[$index]['salesrule_label']) {
            //     $type = 2;
            // }

            if ($type > 0) {
                if ($type == 1) {
                    $discountLabel = (($collection->getData()[$index]['catalogrule_label']) ? $collection->getData()[$index]['catalogrule_label'] : '');
                } else {
                    $discountLabel = (($collection->getData()[$index]['salesrule_label']) ? $collection->getData()[$index]['salesrule_label'] : '');
                }
            }

            $regular = 0;
            $special = 0;
            $regular = $regularPrice->getValue() ? $regularPrice->getValue() : $collection->getData()[$index]['price'];
            $special = $finalPrice->getValue() ? $finalPrice->getValue() : $collection->getData()[$index]['final_price'];
            $regular = $regularPrice->getValue();
            $special = $finalPrice->getValue();

            $item->setId($data->getId());
            $item->setName($data->getName());
            $item->setSku($data->getSku());
            $item->setPrice($regular);
            $item->setDiscountPrice($special);
            $item->setPointLabel('');
            $item->setIsPointProduct(false);

            // if (null !== $data->getCustomAttribute('mp_reward_sell_product')) {
            //     $points = $data->getCustomAttribute('mp_reward_sell_product')->getValue();
            //     if ($points > 0) {
            //         $item->setPrice($points);
            //         $item->setDiscountPrice($points);
            //         $item->setIsPointProduct(true);
            //         if ($points == 1) {
            //             $item->setPointLabel($this->pointHelper->getPointLabel());
            //         } else {
            //             $item->setPointLabel($this->pointHelper->getPluralPointLabel());
            //         }
            //     }
            // }
            $item->setImage($this->getCustomImgUrl($data));
            $item->setDiscountLabel($discountLabel);
            $item->setMpRewardPoints($reward);
            $item->setMpReviewPoints($pointReview);
            $item->setIsFreeShipping($check);
            $item->setFreeShippingImg('');
            $result[] = $item;
            $index++;
        }
        $productList->setItems($result);
        $productList->setPageSize($searchCriteria->getPageSize());
        $productList->setCurrentPage($searchCriteria->getCurrentPage());
        $productList->setTotalCount(0);
        if (count($result) > 0) {
            $productList->setTotalCount($collection->getSize());
        }
        return $productList;
    }

    private function getCustomImgUrl($product)
    {
        $image_url = $this->imageHelper->init($product, 'product_page_image_small')
            ->setImageFile($product->getSmallImage())->resize(194)->getUrl();
        return $image_url;
    }

    /**
     * @param int $categoryId
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * 
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function generateShoppingOptionFilter($categoryId, $collection, SearchCriteriaInterface $searchCriteria)
    {
        $layerNavigationList = $this->customLayerNavigationFactory->create()->getLayerNavigationByCategoryId($categoryId);
        foreach ($layerNavigationList as $navigation) {
            $navigationList[] = $navigation['code'];
        }
        $navigationList[] = 'category_id';
        $filterData = [];
        foreach ($searchCriteria->getFilterGroups() as $key => $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (in_array($filter->getField(), $navigationList)) {
                    $filterData[$filter->getField()] = $filter->getValue();
                }
            }
        }

        $filterableAttributes = $this->filterableAttributeListFactory->create();
        $filterList = $this->filterListFactory->create([
            'filterableAttributes' => $filterableAttributes
        ]);
        $layer = $this->resolverFactory->create()->get();
        $layer->setCurrentCategory($categoryId);

        $filters = $filterList->getFilters($layer);
        foreach ($filters as $filter) {
            $filter->apply($this->requestInterface->setParams($filterData));
        }
        $layer->apply();

        $productIdArr = [];
        $collectionOne = $layer->getProductCollection();
        $total = $collectionOne->getSize();
        foreach ($collectionOne as $item) {
            $productIdArr[] = $item->getId();
        }

        $collection->addFieldToFilter('entity_id', ['in' => $productIdArr]);
        return $collection;
    }
}

