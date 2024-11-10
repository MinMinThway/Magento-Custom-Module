<?php

namespace MMT\Product\Api\Data;

interface DownloadableProductSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get attributes list.
     *
     * @return \MMT\Product\Api\Data\CustomerDownloadableProductManagementInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \MMT\Product\Api\Data\CustomerDownloadableProductManagementInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
