<?php
namespace MMT\Configuration\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct (
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Get notification version
     * @return mixed
     */
    public function getNotificationVersion() {
        return $this->scopeConfig->getValue(
            'mit_configuration/mobile_version_general/notification_version',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get notification message
     * @return mixed
     */
    public function getNotificationMessage() {
        return $this->scopeConfig->getValue(
            'mit_configuration/mobile_version_general/notification_message',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get force update version
     * @return mixed
     */
    public function getForceUpdateVersion() {
        return $this->scopeConfig->getValue(
            'mit_configuration/mobile_version_general/force_update_version',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get force update message
     * @return mixed
     */
    public function getForceUpdateMessage() {
        return $this->scopeConfig->getValue(
            'mit_configuration/mobile_version_general/force_update_message',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get other message
     * @return mixed
     */
    public function getOtherMessage() {
        return $this->scopeConfig->getValue(
            'mit_configuration/mobile_version_general/other_message',
            ScopeInterface::SCOPE_STORE
        );
    }   
    /**Get Store Info Phone Number
     * @return mixed
     */
    public function getStoreInfoPhoneNumber() {
        return $this->scopeConfig->getValue(
            'mit_configuration/store_info_general/store_info_phone_number',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**Get Store Info Mail
     * @return mixed
     */
    public function getStoreInfoMail() {
        return $this->scopeConfig->getValue(
            'mit_configuration/store_info_general/store_info_mail',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**Get Store Info Add
     * @return mixed
     */
    public function getStoreInfoAddress() {
        return $this->scopeConfig->getValue(
            'mit_configuration/store_info_general/store_info_address',
            ScopeInterface::SCOPE_STORE 
        ); 
    } 
}