<?php

namespace MMT\Product\Model\Api;

use MMT\Product\Api\CustomProductByAttrInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use MMT\Product\Api\ProductApiInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use MMT\Product\Model\Api\CustomLayerNavigationFactory;
use Magento\Catalog\Model\Layer\Category\FilterableAttributeListFactory;
use MMT\Product\Model\Layer\FilterListFactory;
use Magento\Catalog\Model\Layer\ResolverFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\Visibility;
use MMT\Product\Model\ProductFactory;
use MMT\Product\Model\ProductResultListFactory;
use Magento\Catalog\Model\CategoryFactory;

class CustomProductByAttr implements CustomProductByAttrInterface
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

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
     * @var Visibility
     */
    private $visibility;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
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
        Visibility $visibility
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
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
        $this->visibility = $visibility;
    }

    public function getAttrProductList($type, SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->visibility->getVisibleInCatalogIds());
        $collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addUrlRewrite();

        if ($type == 1) {
            $collection->addAttributeToSort('created_at', 'desc')
                ->addAttributeToFilter('special_price', ['notnull' => true]);

            $collection->getSelect()->where('price_index.final_price < price_index.price');
        } elseif ($type == 2) {
            $reviewTable =  ('review_entity_summary');
            $collection->addAttributeToSelect('*');
            $collection->joinField('rating_summary', '' . $reviewTable . '', 'rating_summary', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => (int)$this->storeManagerInterface->getStore()->getId()), 'left');

            $collection->joinField('reviews_count', '' . $reviewTable . '', 'reviews_count', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => (int)$this->storeManagerInterface->getStore()->getId()), 'left');

            $collection->setOrder('rating_summary', 'desc');
            $collection->setOrder('reviews_count', 'desc');
        } elseif ($type == 3) {
            $collection->addAttributeToSort(
                'entity_id',
                'desc'
            );
        }

        $collectionIds = array_column($collection->toArray(), 'entity_id');
        if (count($collectionIds) > 0) {
            return $this->getList($type, $searchCriteria, true, 'FIELD(e.entity_id,' . implode(',', $collectionIds) . ')', $collectionIds);
        } else {
            $result = $this->productResultListFactory->create();
            $result->setItems([]);
            $result->setPageSize(0);
            $result->setCurrentPage(0);
            $result->setTotalCount(0);
            return $result;
        }
    }

    public function getList($type, SearchCriteriaInterface $searchCriteria, $customSort = false, $sortQuery = '', $idList)
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
        }
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        // if (count($searchCriteria->getFilterGroups()) > 0) {
        //     foreach ($searchCriteria->getFilterGroups() as $filterGroups) {
        //         foreach ($filterGroups->getFilters() as $filter) {
        //             $collection->addFieldToFilter($filter->getField(), [$filter->getConditionType() => $filter->getValue()]);
        //         }
        //     }
        // }
        $collection->addFieldToFIlter('entity_id', ['in' => $idList]);
        $collection->addMediaGalleryData()
            ->addMinimalPrice()
            ->addFinalPrice();
        $collection->addPriceData();

        // if ($type == 3) {
        //     $collection->getSelect()->where('price_index.final_price < price_index.price');
        // }

        //echo($collection->getSelect()->__toString());
        $result = [];
        $productList = $this->productResultListFactory->create();

        $index = 0;
        foreach ($collection as $data) {
            $item = $this->productFactory->create();
            $check = false;
            $reward = 0;
            $pointReview = 0;
            $regularPrice = $data->getPriceInfo()->getPrice('regular_price')->getAmount();
            $finalPrice   = $data->getPriceInfo()->getPrice('final_price')->getAmount();

            $discountLabel        = '';
            $type = 0;

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
}

