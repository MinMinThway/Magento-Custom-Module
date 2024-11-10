<?php

namespace MMT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MMT\Product\Api\Data\WebsiteDataInterface;

class WebsiteData extends AbstractExtensibleModel implements WebsiteDataInterface
{

    /**
     * @inheritdoc
     */
    public function getIsAccountShare()
    {
        return $this->getData(self::IS_ACCOUNT_SHARE);
    }

    /**
     * @inheritdoc
     */
    public function setIsAccountShare($isAccountShare)
    {
        return $this->setData(self::IS_ACCOUNT_SHARE, $isAccountShare);
    }

    /**
     * @inheritdoc
     */
    public function getWebsites()
    {
        return $this->getData(self::WEBSITES);
    }

    /**
     * @inheritdoc
     */
    public function setWebsites(array $websites)
    {
        return $this->setData(self::WEBSITES, $websites);
    }
}
