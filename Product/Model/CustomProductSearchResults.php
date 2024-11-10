<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MMT\Product\Model;

use Magento\Framework\Api\SearchResults;
use MMT\Product\Api\Data\CustomProductSearchResultsInterface;

/**
 * Service Data Object with Product search results.
 */
class CustomProductSearchResults extends SearchResults implements CustomProductSearchResultsInterface
{
}
