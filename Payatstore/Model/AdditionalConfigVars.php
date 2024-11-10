<?php

namespace MMT\Payatstore\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;

class AdditionalConfigVars implements ConfigProviderInterface
{
    public function getConfig()
    {
        $additionalVariables['pay_at_store'] = 0;
        return $additionalVariables;
    }
}
