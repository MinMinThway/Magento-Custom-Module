<?php

namespace MMT\DetailPopUp\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\HTTP\Client\Curl;

class Popup extends Template
{
    protected $registry;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * Constructor.
     *
     * @param Curl $curl
     */
    public function __construct(
        Curl $curl,
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->curl = $curl;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }


    const URL = 'https://api2.branch.io/v1/url';

    public function getPopupUrl()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/DetailPopUp.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('logger start');

        $product_id = $this->getCurrentProductId();
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
                    "page_name" => "product-detail",
                    "product_id" => $product_id
                ]
            ]
        ];

        $url = "https://api2.branch.io/v1/url";

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

    public function getCurrentProductId()
    {
        $product = $this->registry->registry('current_product');
        if ($product) {
            return $product->getId();
        }
        return '';
    }
}
