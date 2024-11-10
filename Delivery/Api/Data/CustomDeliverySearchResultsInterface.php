<?php

namespace MMT\Delivery\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CustomDeliverySearchResultsInterface extends SearchResultsInterface
{

    public function getItems();

    public function setItems(array $items = null);
}
