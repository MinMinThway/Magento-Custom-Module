<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MMT\Customer\Plugin\Magento\Customer\Block\Account;

use Magento\Store\Model\StoreManagerInterface;

class AuthenticationPopup
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    public function __construct(
        StoreManagerInterface $storeManagerInterface
    )
    {
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function afterGetConfig(
        \Magento\Customer\Block\Account\AuthenticationPopup $subject,
        $result
    ) {
        $result['storeCode'] = $this->storeManagerInterface->getStore()->getCode();
        return $result;
    }
}

