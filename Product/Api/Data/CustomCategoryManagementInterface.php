<?php

namespace MMT\Product\Api\Data;

use Magento\Catalog\Api\Data\CategoryInterface;

interface CustomCategoryManagementInterface extends CategoryInterface
{

    /**
     * set child of category
     * @param \MMT\Product\Api\Data\CustomCategoryManagementInterface[] $categoryList
     * @return $this
     */
    public function setCustomChildren(array $categoryList = null);

    /**
     * get child of category
     * @return \MMT\Product\Api\Data\CustomCategoryManagementInterface[]|[]
     */
    public function getCustomChildren();
}
