<?php

namespace MMT\Product\Model\Api;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Visibility as CatalogProductVisibility;
use Magento\Rule\Model\Condition\Sql\Builder as SqlBuilder;
use MMT\Product\Api\CustomNewProductInterface;
use MMT\Product\Api\Data\CustomProductSearchResultsInterfaceFactory;
use MMT\Product\Helper\ProductWidgetHelper;
use Magento\Framework\Api\SortOrderBuilder;
use MMT\Product\Model\Api\ProductApi;
use MMT\Product\Api\Data\ProductResultListInterfaceFactory;

class CustomNewProduct implements CustomNewProductInterface
{
    const NEW_PRODUCT_BLOCK_EN = 'new-products';
    const NEW_PRODUCT_BLOCK_MM = 'new-products-for-mm';
    const NEW_PRODUCT_BLOCK_PAGE = 'new-products';
    const BUNDLE_PRODUCT_BLOCK_EN = 'bundle-products';
    const BUNDLE_PRODUCT_BLOCK_MM = 'bundle-products-for-mm';
    const BUNDLE_PRODUCT_BLOCK_PAGE = 'bundle-products';
    const SORT_DATA_ARR = array(
        'position' => array('columns' => 'entity_id', 'direction' => 'desc'),
        'date_newest_top' => array('columns' => 'created_at', 'direction' => 'desc'),
        'date_oldest_top' => array('columns' => 'created_at', 'direction' => 'asc'),
        'name_ascending' => array('columns' => 'name', 'direction' => 'asc'),
        'name_descending' => array('columns' => 'name', 'direction' => 'desc'),
        'sku_ascending' => array('columns' => 'sku', 'direction' => 'asc'),
        'sku_descending' => array('columns' => 'sku', 'direction' => 'desc'),
        'price_high_to_low' => array('columns' => 'price', 'direction' => 'desc'),
        'price_low_to_high' => array('columns' => 'price', 'direction' => 'asc'),
    );

    /**
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomProduct
     */
    private $customProduct;

    /**
     * @var CustomProductSearchResultsInterfaceFactory
     */
    private $customProductSearchResultsInterface;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepositoryInterface;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CatalogProductVisibility
     */
    private $catalogProductVisibility;

    /**
     * @var SqlBuilder
     */
    private $sqlBuilder;

    /**
     * @var ProductWidgetHelper
     */
    private $productWidgetHelper;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     *@var ProductApi
    */
    protected $productApi;

      /**
     * @var ProductResultListInterfaceFactory
     */
    protected $productResultListInterface;

    public function __construct(
        BlockRepository $blockRepository,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomProduct $customProduct,
        CustomProductSearchResultsInterfaceFactory $customProductSearchResultsInterface,
        PageRepositoryInterface $pageRepositoryInterface,
        ProductCollectionFactory $productCollectionFactory,
        CatalogProductVisibility $catalogProductVisibility,
        SqlBuilder $sqlBuilder,
        ProductWidgetHelper $productWidgetHelper,
        SortOrderBuilder $sortOrderBuilder,
        ProductApi $productApi,
        ProductResultListInterfaceFactory $productResultListInterface
    ) {
        $this->blockRepository = $blockRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->customProduct = $customProduct;
        $this->customProductSearchResultsInterface = $customProductSearchResultsInterface;
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->sqlBuilder = $sqlBuilder;
        $this->productWidgetHelper = $productWidgetHelper;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->productApi = $productApi;
        $this->productResultListInterface = $productResultListInterface;
    }


    /**
     * get new product list for homepage
     * 
     * @inheritdoc
     */
    public function getNewProductList(SearchCriteriaInterface $searchCriteria)
    {
        $array = [];
        $blockContent = $this->getBlockContent(self::NEW_PRODUCT_BLOCK_EN, self::NEW_PRODUCT_BLOCK_MM);
        $array = [
            ['id' => 'position', 'columns' => 'entity_id', 'direction' => 'desc'],
            ['id' => 'date_newest_top', 'columns' => 'created_at', 'direction' => 'desc'],
            ['id' => 'date_oldest_top', 'columns' => 'created_at', 'direction' => 'asc'],
            ['id' => 'name_ascending', 'columns' => 'name', 'direction' => 'asc'],
            ['id' => 'name_descending', 'columns' => 'name', 'direction' => 'desc'],
            ['id' => 'sku_ascending', 'columns' => 'sku', 'direction' => 'asc'],
            ['id' => 'sku_descending', 'columns' => 'sku', 'direction' => 'desc'],
            ['id' => 'price_high_to_low', 'columns' => 'price', 'direction' => 'desc'],
            ['id' => 'price_low_to_high', 'columns' => 'price', 'direction' => 'asc'],
        ];

        if (isset($blockContent) && $blockContent) {
            $dataArr = $this->productWidgetHelper->getConditionsAndProductCounts($blockContent);

            $collection = $this->productCollectionFactory->create();
            $collection->setStoreId($this->storeManager->getStore()->getId());
            $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
            $collection
                ->addAttributeToSelect(['entity_id'])
                ->addUrlRewrite()
                ->addStoreFilter()
                ->setPageSize(100)
                ->setCurPage(1);

            if (array_key_exists($dataArr['sortOrder'], self::SORT_DATA_ARR)) {
                $collection->addAttributeToSort(self::SORT_DATA_ARR[$dataArr['sortOrder']]['columns'], self::SORT_DATA_ARR[$dataArr['sortOrder']]['direction']);
            } else {
                $collection->addAttributeToSort('created_at', 'desc');
            }

            $conditions = $this->productWidgetHelper->getConditions($dataArr['conds']);
            $conditions->collectValidatedAttributes($collection);
            $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
            $collection->distinct(true);

            $ids = [];

            foreach ($collection as $item) {
                $ids[] = $item->getId();
            }
            $searchCriteria->setPageSize($dataArr['count']);
            $searchCriteria->setCurrentPage(1);

            $filteredId = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('entity_id')
                ->setValue($ids)
                ->create();

            $filterGroupList = $searchCriteria->getFilterGroups();
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $searchCriteria->setFilterGroups($filterGroupList);

            $searchResult = $this->productApi->getList(0,$searchCriteria, true, 'FIELD(e.entity_id,' . implode(',', $ids) . ')');
            $searchResult->setTotalCount($collection->getSize());
            return $searchResult;
        }
        return $this->customProductSearchResultsInterface->create();
    }

    /**
     * get new product list for new product page
     * 
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $productCounts = 0;
        $searchCriteriaBuilder = $this->_searchCriteriaBuilder->addFilter('identifier', self::NEW_PRODUCT_BLOCK_PAGE, 'eq')->create();
        $pages = $this->pageRepositoryInterface->getList($searchCriteriaBuilder)->getItems();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if ($page->getContent()) {
                    $index = strpos($page->getContent(), 'products_count');
                    $new = substr($page->getContent(), $index + 16, strlen($page->getContent()));
                    $lastIdx = strpos($new, 'template');
                    $productCounts = substr($new, 0, $lastIdx - 2);
                }
                $sorterArr = $searchCriteria->getSortOrders();
                $sorterArr[] = $this->sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();
                $searchCriteria->setSortOrders($sorterArr);
                $list = $this->productApi->getList(0, $searchCriteria);

                if ($productCounts > 0) {
                    if ($list->getTotalCount() >= $productCounts) {
                        $list->setTotalCount($productCounts);
                    }
                    if ($productCounts < ($searchCriteria->getPageSize() * $searchCriteria->getCurrentPage())) {
                        $itemCount = ($searchCriteria->getPageSize() * $searchCriteria->getCurrentPage()) - $productCounts;
                        // count 200, size= 205, pagesize = 10:
                        if ($itemCount > $searchCriteria->getPageSize()) {
                            $list->setItems([]);
                        }
                    }
                }
                return $list;
            }
        }
        return [];
    }


    /**
     * @inheritdoc
     */
    public function getBundleProductList(SearchCriteriaInterface $searchCriteria)
    {
        $blockContent = $this->getBlockContent(self::BUNDLE_PRODUCT_BLOCK_EN, self::BUNDLE_PRODUCT_BLOCK_MM);
        if (isset($blockContent) && $blockContent) {
            $dataArr = $this->productWidgetHelper->getConditionsAndProductCounts($blockContent);
            $collection = $this->productCollectionFactory->create();
            $collection->setStoreId($this->storeManager->getStore()->getId());
            $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
            $collection
                ->addAttributeToSelect(['entity_id', 'created_at'])
                ->addUrlRewrite()
                ->addStoreFilter()
                ->setPageSize(100)
                ->setCurPage(1);

            if (array_key_exists($dataArr['sortOrder'], self::SORT_DATA_ARR)) {
                $collection->addAttributeToSort(self::SORT_DATA_ARR[$dataArr['sortOrder']]['columns'], self::SORT_DATA_ARR[$dataArr['sortOrder']]['direction']);
            } else {
                $collection->addAttributeToSort('created_at', 'desc');
            }

            $conditions = $this->productWidgetHelper->getConditions($dataArr['conds']);
            $conditions->collectValidatedAttributes($collection);
            $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
            $collection->distinct(true);

            $ids = [];

            foreach ($collection as $item) {
                $ids[] = $item->getId();
            }

            $searchCriteria->setPageSize($dataArr['count']);
            $searchCriteria->setCurrentPage(1);

            $filteredId = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('entity_id')
                ->setValue($ids)
                ->create();

            $filterGroupList = $searchCriteria->getFilterGroups();
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $searchCriteria->setFilterGroups($filterGroupList);

            return $this->productApi->getList(0 ,$searchCriteria, true, 'FIELD(e.entity_id,' . implode(',', $ids) . ')');
        }
        return $this->customProductSearchResultsInterface->create();
    }


    /**
     * @inheritdoc
     */
    public function getBundleProductListForPage(SearchCriteriaInterface $searchCriteria)
    {
        $searchCriteriaBuilder = $this->_searchCriteriaBuilder->addFilter('identifier', self::BUNDLE_PRODUCT_BLOCK_PAGE, 'eq')->create();
        $pages = $this->pageRepositoryInterface->getList($searchCriteriaBuilder)->getItems();
        if (count($pages) > 0) {
            foreach ($pages as $page) {
                if ($page->getContent()) {
                    $dataArr = $this->productWidgetHelper->getConditionsAndProductCounts($page->getContent());
                    $collection = $this->productCollectionFactory->create();
                    $collection->setStoreId($this->storeManager->getStore()->getId());
                    $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
                    $collection
                        ->addAttributeToSelect('entity_id')
                        ->addUrlRewrite()
                        ->addStoreFilter()
                        ->addAttributeToSort('entity_id', 'desc')
                        ->setPageSize(100)
                        ->setCurPage(1);

                    $conditions = $this->productWidgetHelper->getConditions($dataArr['conds']);
                    $conditions->collectValidatedAttributes($collection);
                    $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
                    $collection->distinct(true);

                    $ids = [];

                    foreach ($collection as $item) {
                        $ids[] = $item->getId();
                    }

                    $filteredId = $this->_filterBuilder
                        ->setConditionType('in')
                        ->setField('entity_id')
                        ->setValue($ids)
                        ->create();

                    $filterGroupList = $searchCriteria->getFilterGroups();
                    $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                    $searchCriteria->setFilterGroups($filterGroupList);


                    $searchResult = $this->customProduct->getList($searchCriteria, true, 'FIELD(e.entity_id,' . implode(',', $ids) . ')');
                    $searchResult->setTotalCount($collection->getSize());
                    return $searchResult;
                }
            }
        }
        return $this->productResultListInterface->create();
    }


    /**
     * get block content
     * @param String $identifierEn
     * @param String $identifierMm
     * @return String
     */
    private function getBlockContent($identifierEn = self::NEW_PRODUCT_BLOCK_EN, $identifierMm = self::NEW_PRODUCT_BLOCK_MM)
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        if ($storeCode == 'mm') {
            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('identifier')
                ->setValue($identifierMm)
                ->create();
        } else {
            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('identifier')
                ->setValue($identifierEn)
                ->create();
        }


        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $blockData = $this->blockRepository->getList($this->_searchCriteriaBuilder->create());
        foreach ($blockData->getItems() as $data) {
            if ($data) {
                return $data->getContent();
            }
        }
        return '';
    }
}
