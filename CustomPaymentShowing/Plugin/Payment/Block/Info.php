<?php

namespace MMT\CustomPaymentShowing\Plugin\Payment\Block;

class Info
{

    public function beforeToHtml(\Magento\Payment\Block\Info $subject)
    {
        if ($subject->getTemplate() === 'Magento_Payment::info/default.phtml') {
            $subject->setTemplate('MMT_CustomPaymentShowing::info/default.phtml');
        } else if ($subject->getTemplate() === 'Magento_Payment::info/pdf/default.phtml') {
            $subject->setTemplate('MMT_CustomPaymentShowing::info/pdf/default.phtml');
        }
    }
}
