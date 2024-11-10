<?php
namespace MMT\Configuration\Model\Api;

use MMT\Configuration\Api\ConfigurationApiInterface;
use MMT\Configuration\Helper\Data; 

class ConfigurationApi implements ConfigurationApiInterface 
{ 
    protected $dataHelper;

    /**
     * @var Data
     */ 
    public function __construct(        
        Data $dataHelper
    ) {       
        $this->dataHelper = $dataHelper;
    }    
    /**
     * @inheritdoc
     */
    public function checkMobileVersion($version_number) {
        $force_update_version = $this->dataHelper->getForceUpdateVersion(); 
        $force_update_message = $this->dataHelper->getForceUpdateMessage(); 
        
        $notification_version = $this->dataHelper->getNotificationVersion(); 
        $notification_message = $this->dataHelper->getNotificationMessage(); 

        $other_message = $this->dataHelper->getOtherMessage();  

        $response = ['status' => 1 ];
        if ($version_number <= $force_update_version ) {
            $response = ['status' => 1, 'message' => $force_update_message ];           
        } else if ($version_number <= $notification_version ) {
            $response = ['status' => 2, 'message' => $notification_message ];           
        } else {
            $response = ['status' => 3, 'message' => $other_message ];
        } 
        return array($response); 
    }
     /**
     * @inheritdoc
    **/    
    public function getStoreInfo() {
        $getStoreInfoPhoneNumber = $this->dataHelper->getStoreInfoPhoneNumber(); 
        $getStoreInfoAddress = $this->dataHelper->getStoreInfoAddress(); 
        $getStoreInfoMail = $this->dataHelper->getStoreInfoMail(); 
        $response = [
            'phone_number' => $getStoreInfoPhoneNumber,
            'address' => $getStoreInfoAddress,
            'mail' => $getStoreInfoMail
        ]; 
        return array($response);   
    }
} 