<?php

namespace MMT\Checkout\Plugin;

use Magento\Checkout\Block\Checkout\AttributeMerger;

class PhonePlugin
{
    /**
     * @param AttributeMerger $subject
     * @param $result
     * @return mixed
     */
    public function afterMerge(AttributeMerger $subject, $result)
    {
        $result['telephone']['validation'] = [
            'required-entry'  => true,
            'validate-number' => true,
            'validate-phone-number-custom' => true
        ];
        return $result;
    }
}