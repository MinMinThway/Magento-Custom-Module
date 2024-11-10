<?php

namespace MMT\Product\Api;

interface WebsiteInterface {

    /**
     * get all availabel website data
     * @return \MMT\Product\Api\Data\WebsiteDataInterface
     */
    public function getWebsites();
}