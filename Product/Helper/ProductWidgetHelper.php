<?php

namespace MMT\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Widget\Helper\Conditions;
use Magento\CatalogWidget\Model\Rule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductWidgetHelper extends AbstractHelper
{
    /**
     * @var Conditions
     */
    private $conditions;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var Rule
     */
    private $rule;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        Context $context,
        Conditions $conditions,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        Rule $rule,
        StoreManagerInterface $storeManagerInterface
    ) {
        parent::__construct($context);
        $this->conditions = $conditions;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->rule = $rule;
        $this->storeManagerInterface = $storeManagerInterface;
    }


    /**
     * get condition str and total product count str from cms block or page content
     * @param String $content
     * @return array
     */
    public function getConditionsAndProductCounts($content)
    {
        $start = strpos($content, '{{');
        $end = strpos($content, '}}');

        $updatedStr = substr($content, $start + 2, $end - $start);
        $str = substr($updatedStr, strpos($updatedStr, 'conditions_encoded') + strlen('conditions_encoded="'));
        $conditions = substr($str, 0, strpos($str, '"'));

        $productCountStr = substr($updatedStr, strpos($updatedStr, 'products_count') + strlen('products_count="'));
        $productCount = substr($productCountStr, 0, strpos($productCountStr, '"'));

        $sortOrder = substr($updatedStr, strpos($updatedStr, 'sort_order') + strlen('sort_order="'));
        $productSortOrder = substr($sortOrder, 0, strpos($sortOrder, '"'));

        return array('conds' => $conditions, 'count' => $productCount, 'sortOrder' => $productSortOrder);
    }


    /**
     * get condition
     * @param String $conditions
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions($conditions)
    {
        if ($conditions) {
            $conditions = $this->conditions->decode($conditions);
        }

        foreach ($conditions as $key => $condition) {
            if (!empty($condition['attribute'])) {
                if (in_array($condition['attribute'], ['special_from_date', 'special_to_date'])) {
                    $conditions[$key]['value'] = date('Y-m-d H:i:s', strtotime($condition['value']));
                }

                if ($condition['attribute'] == 'category_ids') {
                    $conditions[$key] = $this->updateAnchorCategoryConditions($condition);
                }
            }
        }
        $rule = $this->rule->loadPost(['conditions' => $conditions]);
        return $rule->getConditions();
    }

    /**
     * Update conditions if the category is an anchor category
     *
     * @param array $condition
     * @return array
     */
    private function updateAnchorCategoryConditions(array $condition): array
    {
        if (array_key_exists('value', $condition)) {
            $categoryId = $condition['value'];

            try {
                $category = $this->categoryRepositoryInterface->get($categoryId, $this->storeManagerInterface->getStore()->getId());
            } catch (NoSuchEntityException $e) {
                return $condition;
            }

            $children = $category->getIsAnchor() ? $category->getChildren(true) : [];
            if ($children) {
                $children = explode(',', $children);
                $condition['operator'] = "()";
                $condition['value'] = array_merge([$categoryId], $children);
            }
        }

        return $condition;
    }
}

