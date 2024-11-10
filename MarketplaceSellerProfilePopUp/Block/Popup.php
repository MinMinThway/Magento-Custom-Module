<?php

namespace MMT\MarketplaceSellerProfilePopUp\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\HTTP\Client\Curl;
use Webkul\Marketplace\Helper\Data as MpHelper;


class Popup extends Template
{
    protected $registry;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * Constructor.
     *
     * @param Curl $curl
     * @param MpHelper $mpHelper
     */
    public function __construct(
        Curl $curl,
        Template\Context $context,
        Registry $registry,
        MpHelper $mpHelper,
        array $data = []
    ) {
        $this->curl = $curl;
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->mpHelper = $mpHelper;
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Webkul\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        $helper = $this->mpHelper;
        return $helper->getProfileDetail(MpHelper::URL_TYPE_COLLECTION);
    }



    public function getSellerIdPopUp()
    {
        $partner = $this->getProfileDetail();
        try {
            $sellerId = $partner->getSellerId();
        } catch (\Exception $e) {
            $sellerId = 0;
        }
        
        return $sellerId; 

    }

    const URL = 'https://api2.branch.io/v1/url';

    public function getPopupUrl()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DetailPopUp.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('logger start');

        $sellerId = $this->getSellerIdPopUp();
        $params = [
            'branch_key' => "key_live_kFga1hpNQwOueC08NsYwxihguscPID8w",
            "channel" => "facebook",
            "feature" => "onboarding",
            "campaign" => "new product",
            "stage" => "new user",
            "tags" => [
                "one"
            ],
            "data" => [
                "custom_object" => [
                    "page_name" => "seller-profile",
                    "product_id" => $sellerId
                ]
            ]
        ];


        
        $customCurl = curl_init(self::URL);
        curl_setopt_array($customCurl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));

        $response = curl_exec($customCurl);

        $logger->info('response' . $response);


        // Check for errors
        if ($response === FALSE) {
            die(curl_error($customCurl));
        }

        // Decode the response
        $responseData = json_decode($response, TRUE);

        if ($responseData['url']) {
            $logger->info($response);
        }

        return $response;

        // Close the cURL handler
        curl_close($customCurl);
    }

}
