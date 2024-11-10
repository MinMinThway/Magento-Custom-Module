<?php

namespace MMT\ProductSorter\Api;

interface ProductSortOrderInterface
{
    /**
     * get available product sort order
     * @return array
     */
    public function getProductSortOrder();
}
