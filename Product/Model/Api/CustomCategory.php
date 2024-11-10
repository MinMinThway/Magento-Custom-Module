<?php

namespace MMT\Product\Model\Api;

use Magento\Catalog\Model\CategoryList;
use Magento\Catalog\Model\CategoryManagement;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\ProductCategoryList;
use MMT\Product\Api\CustomCategoryInterface;
use MMT\Product\Model\CustomCategoryFactory;
use MMT\Product\Helper\ImageHelper;

class CustomCategory implements CustomCategoryInterface
{

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CustomCategoryFactory
     */
    protected $customCategoryFactory;

    protected $blockRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    protected $categoryList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Grouped
     */
    protected $grouped;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var ProductCategoryList
     */
    private $productCategoryList;

    /**
     * @var CategoryManagement
     */
    private $categoryManagement;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CustomCategoryFactory $customCategoryFactory,
        BlockRepository $blockRepository,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        CategoryList $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory,
        Grouped $grouped,
        Configurable $configurable,
        ProductCategoryList $productCategoryList,
        CategoryManagement $categoryManagement,
        ImageHelper $imageHelper
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->customCategoryFactory = $customCategoryFactory;
        $this->blockRepository = $blockRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->categoryList = $categoryList;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->grouped = $grouped;
        $this->configurable = $configurable;
        $this->productCategoryList = $productCategoryList;
        $this->categoryManagement = $categoryManagement;
        $this->imageHelper = $imageHelper;
    }

    /**
     * get category by id including child data
     * @param int $id
     * @return \MMT\Product\Api\Data\CustomCategoryManagementInterface
     */
    public function getCategoryById($id)
    {
        $factory = $this->customCategoryFactory->create();
        $model = $factory->load($id);
        return $model;
        // if ($model->getIsActive() && $model->getIncludeInMenu()) {
        //     return $model;
        // }
    }

    public function getCategoryListForHomePage(SearchCriteriaInterface $searchCriteria)
    {
        // $block = $this->blockRepository->getById($id);
        $blockContent = $this->getBlockContent();
        if (isset($blockContent)) {
            $categoryIdx = strpos($blockContent, "categories=\"");
            $content = substr($blockContent, $categoryIdx, -1);
            $subContent = substr($content, strlen('categories="'), -1);
            $result = substr($subContent, 0, strpos($subContent, '"'));
            $categoryIds = explode(',', $result);

            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('entity_id')
                ->setValue($categoryIds[0])
                ->create();

            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $currentStore = $this->storeManager->getStore();
            $baseUrl = $currentStore->getBaseUrl();

            $searchCriteria->setFilterGroups($filterGroupList);
            $result = $this->categoryList->getList($searchCriteria);
            foreach ($result->getItems() as $item) {
                if ($item->getCustomAttributes()) {
                    foreach ($item->getCustomAttributes() as $customAttribute) {
                        if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                            $customAttribute->setValue($baseUrl . $customAttribute->getValue());
                        }
                    }
                }
            }


            $dataList = $result->getItems();

            $index = 0;
            foreach($categoryIds as $id) {
                if ($index > 0) {
                    $filteredId = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField('entity_id')
                    ->setValue($id)
                    ->create();
    
                    $filterGroupList = [];
                    $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                    $searchCriteria->setFilterGroups($filterGroupList);
                    $res = $this->categoryList->getList($searchCriteria);
                    if ($res->getTotalCount() > 0) {
                        foreach($res->getItems() as $item) {
                            if ($item->getCustomAttributes()) {
                                foreach ($item->getCustomAttributes() as $customAttribute) {
                                    if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                                        $customAttribute->setValue($baseUrl . $customAttribute->getValue());
                                    }
                                }
                            }
                            $dataList[] = $item;
                        }
                    }
                }
                $index++;
            }
            $result->setItems($dataList);

	    return $result;
        }
    }

    /**
     * @inheritDoc
     */
    public function getPopularCategory()
    {
        $result = [];
        $currentStore = $this->storeManager->getStore();
        $baseUrl = $currentStore->getBaseUrl();
        $collection = $this->productCollectionFactory->create();
        $collection->getSelect()->join('sales_bestsellers_aggregated_yearly as viewed_item', 'e.entity_id = viewed_item.product_id', 'qty_ordered');
        $collection->getSelect()->columns(['viewed_item.qty_ordered' => new \Zend_Db_Expr('SUM(viewed_item.qty_ordered)')]);
        $collection->addStoreFilter($this->storeManager->getStore()->getId());
        $collection->getSelect()
            ->order('SUM(viewed_item.qty_ordered) desc')
            ->group('viewed_item.product_id');

        $productIds = $this->getProductParentIds($collection);
        $parentCategoryIds = $this->getFirstLevelCategoryIds();
        $categoryIds = [];

        foreach($productIds as $productId) {
            $productCategories = $this->getCategoryIds($productId);
            $matchCategoryIdArr = array_intersect($parentCategoryIds, $productCategories);

            if (count($matchCategoryIdArr) > 0) {
                $categoryIds[] = array_values($matchCategoryIdArr)[0];
            } else {
                $allProductCategories = $this->getAllParentCategoriesByIds($productCategories);
                $matchAllCategoryIdArr = array_intersect($parentCategoryIds, $allProductCategories);
                if(count($matchAllCategoryIdArr) > 0) {
                    $categoryIds[] = array_values($matchAllCategoryIdArr)[0];
                }
            }
        }

        $categoryIds = array_slice(array_unique($categoryIds), 0, 6);
        if (count($categoryIds) == 0) {
            $categoryIds = array_slice(array_unique($parentCategoryIds), 0, 6);
        }
        foreach($categoryIds as $categoryId) {
            $category = $this->customCategoryFactory->create()->load($categoryId);
            $data = [];
                $data['id'] = $category->getId();
                $data['name'] = $category->getName();
                $data['img_path'] = '';
                if ($category->getCustomAttributes()) {
                    foreach ($category->getCustomAttributes() as $customAttribute) {
                        if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                            $imgPath = str_replace('/media/', '', $customAttribute->getValue());
                            $imgArr = explode('/', $imgPath);
                            $imgDir = str_replace(end($imgArr), '', $imgPath);
                            $data['img_path'] = $this->imageHelper->resize(end($imgArr), $imgDir, 'catalog/category/resize/');
                            //$data['img_path'] = $baseUrl . $customAttribute->getValue();
                        }
                    }
                }
                $result[] = $data;
        }
        return $result;
    }

    private function getBlockContent()
    {
        $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue('category_list_block')
            ->create();

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

    /**
     * @param $collection best seller collection
     *
     * @return array $productIds
     */
    public function getProductParentIds($collection)
    {
        $productIds = [];
        foreach ($collection as $product) {
            if (isset($product->getData()['entity_id'])) {
                $productId = $product->getData()['entity_id'];
            } else {
                $productId = $product->getProductId();
            }
            $parentIdsGroup  = $this->grouped->getParentIdsByChild($productId);
            $parentIdsConfig = $this->configurable->getParentIdsByChild($productId);
            if (!empty($parentIdsGroup)) {
                $productIds[] = $parentIdsGroup[0];
            } elseif (!empty($parentIdsConfig)) {
                $productIds[] = $parentIdsConfig[0];
            } else {
                $productIds[] = $productId;
            }
        }
        return $productIds;
    }

    /**
     * get unique category ids from product id list
     * @param int $productId
     * @return array
     */
    private function getCategoryIds($productId) {
        return $this->productCategoryList->getCategoryIds($productId);
    }

    /**
     * get first level category
     * @return array
     */
    private function getFirstLevelCategoryIds() {
        $parentIds = [];
        $result = $this->categoryManagement->getTree();
        $menuList = $result->getChildrenData();
        foreach($menuList as $menu) {
            $parentIds[] = $menu->getId();
        }
        return $parentIds;
    }

    /**
     * get all parent category ids from product category ids
     * @return array
     */
    private function getAllParentCategoriesByIds($categoryIds) {
        $updateCategoryList = [];
        $categoryCollectionResult = $this->customCategoryFactory->create()->getCollection()->addFieldToFilter('entity_id', ['in' => $categoryIds]);
        foreach($categoryCollectionResult as $cate) {
            $parentCategories = $cate->getParentCategories();
            foreach ($parentCategories as $parentCategory) {
                $updateCategoryList[] = $parentCategory->getId();
            }
        }
        return $updateCategoryList;
    }
}

