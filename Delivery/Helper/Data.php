<?php

namespace MMT\Delivery\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const NAME_FILE  = 'carriers/customshipping/city_file_upload';

    /** @var ScopeConfigInterface  */
    public $scopeConfig;

    /** @var StoreManagerInterface  */
    public $storeManager;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param string $configPath
     * @return bool
     */
    public function getScopeConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigFileName($nameFile = self::NAME_FILE)
    {
        return $this->getScopeConfig($nameFile);
    }
}
