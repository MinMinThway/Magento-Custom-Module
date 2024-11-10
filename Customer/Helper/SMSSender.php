<?php

namespace MMT\Customer\Helper;

use Magento\Framework\App\Helper\Context;

class SMSSender extends \Magento\Framework\App\Helper\AbstractHelper
{
//     const SMS_URL = 'http://api.vmgmyanmar.com/api/SMSBrandname/SendSMS';
//     const TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c24iOiJnbXAiLCJzaWQiOiIwYTJhYmQzOC1mMmVhLTRiMTItYjE5Zi03Mjk4MGU3N2U2N2IiLCJvYnQiOiIiLCJvYmoiOiIiLCJuYmYiOjE2NzQ0NTg4OTYsImV4cCI6MTY3NDQ2MjQ5NiwiaWF0IjoxNjc0NDU4ODk2fQ.fvtnaVbkhKQlTtUIDgckZWwPuAXnb957fP6V0MDXaVg'; 

//    public function __construct(Context $context)
//     {
//         parent::__construct($context);
//     }

//     public function sendSMS($phoneNumber, $message)
//     {
//         $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sms.log');
//         $logger = new \Zend_Log();
//         $logger->addWriter($writer);
//         $logger->info('sender builder called');
//         $logger->info($phoneNumber);
//         $logger->info($message);
//         $params = [
//             'to' => str_replace('+', '', $phoneNumber),
//             'type' => 1,
//             'from' => 'GaMonePwint',
//             'requestId' => '',
//             'scheduled' => '',
//             'useUnicode' => 1,
//             'exit' => '',
//             'message' => $message,
// 	];

// 	$logger->info(self::SMS_URL);

//         $customCurl = curl_init(self::SMS_URL);
//         curl_setopt_array($customCurl, array(
//             CURLOPT_POST => TRUE,
//             CURLOPT_RETURNTRANSFER => TRUE,
//             CURLOPT_HTTPHEADER => array(
//                 'Content-Type: application/json',
//                 'token: ' . self::TOKEN
//             ),
//             CURLOPT_POSTFIELDS => json_encode($params)
//         ));

//         $response = curl_exec($customCurl);

//         // Check for errors
//         if ($response === FALSE) {
//             die(curl_error($customCurl));
//         }

//         // Decode the response
//         $responseData = json_decode($response, TRUE);

//         if ($responseData['errorCode']) {
//             $logger->info($response);
//         }

//         // Close the cURL handler
//         curl_close($customCurl);


    const SMS_URL = 'https://mxgw.omnicloudapi.com/sms/sendmessage';
    const APP_ID = '008';
    const ACCESS_KEY = '191d206ec38e83c1';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function sendSMS($phoneNumber, $message)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sms.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('sender builder called');
        $logger->info($phoneNumber);
        $logger->info($message);
        $params = [
            'phoneno' => $phoneNumber,
            'appid' => self::APP_ID,
            'accesskey' => self::ACCESS_KEY,
            'message' => $message . '(ConnectMM)',
            'sender_info' => 'ConnectMM'
        ];

        $customCurl = curl_init(self::SMS_URL);
        curl_setopt_array($customCurl, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($params)
        ));

        $response = curl_exec($customCurl);

        // Check for errors
        if ($response === FALSE) {
            die(curl_error($customCurl));
        }

        // Decode the response
        $responseData = json_decode($response, TRUE);

        // Close the cURL handler
        curl_close($customCurl);
    }
}
