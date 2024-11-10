<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MMT\Cart\Plugin\Magento\Quote\Api;

use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartItemExtensionInterfaceFactory;
use Magento\Quote\Model\QuoteRepository;

class CartItemRepositoryInterface
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var CartItemExtensionInterfaceFactory
     */
    private $cartItemExtensionInterfaceFactory;

    public function __construct(
        QuoteRepository $quoteRepository,
        CartItemExtensionInterfaceFactory $cartItemExtensionInterfaceFactory
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->cartItemExtensionInterfaceFactory = $cartItemExtensionInterfaceFactory;
    }

    public function afterSave(
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartItemInterface  $result,
        $cartItem
    ) {
        //Your plugin code
        $quote = $this->quoteRepository->get($result->getQuoteId());
        $extensionAttributes = $result->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->cartItemExtensionInterfaceFactory->create();
        }
        $extensionAttributes->setTotalCount($quote->getItemsQty());
        $result->setExtensionAttributes($extensionAttributes);
        return $result;
    }
}
