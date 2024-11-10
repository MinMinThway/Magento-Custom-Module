<?php

namespace MMT\Product\Api\Data;

interface WebsiteDataInterface {
    const IS_ACCOUNT_SHARE = 'is_account_share';
    const WEBSITES = 'websites';

    /**
     * get is account share
     * @return bool
     */
    public function getIsAccountShare();

    /**
     * set is account share
     * @param bool $isAccountShare
     * @return $this
     */
    public function setIsAccountShare($isAccountShare);

    /**
     * get websites
     * @return \MMT\Product\Api\Data\CustomWebsiteDataInterface[]
     */
    public function getWebsites();

    /**
     * set websites
     * @param \MMT\Product\Api\Data\CustomWebsiteDataInterface[] $websites
     * @return $this
     */
    public function setWebsites(array $websites);
}