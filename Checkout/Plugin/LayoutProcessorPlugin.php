<?php

namespace MMT\Checkout\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPlugin
{
    /**
     * @param LayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(
        LayoutProcessor $subject,
        $jsLayout
    ) {
        //Remove telephone tooltip
        unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config']['tooltip']);



        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation']['validate-phone-number-custom'] = true;


        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment) {
            /* Telephone Billing Address */
            if (isset($payment['children']['form-fields']['children']['telephone'])) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']['telephone']['validation'] = ['required-entry' => true, 'validate-phone-number-custom' => true];
            }
        }

        return $jsLayout;
    }
}
