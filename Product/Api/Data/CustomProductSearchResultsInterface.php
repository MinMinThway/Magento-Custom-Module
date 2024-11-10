<?php

namespace MMT\Product\Api\Data;

interface CustomProductSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get attributes list.
     *
     * @return \MMT\Product\Api\Data\CustomProductManagementInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \MMT\Product\Api\Data\CustomProductManagementInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
