<?php

namespace MMT\Product\Api\Data;

interface ReviewManagementSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \MMT\Product\Api\Data\ReviewManagementInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \MMT\Product\Api\Data\ReviewManagementInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
