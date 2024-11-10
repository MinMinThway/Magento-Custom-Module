<?php

namespace MMT\Cart\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use MMT\Cart\Model\DeliveryDateOptionsFactory;

class CustomCartRepository
{
    protected $cartItemExtensionFactory;

    /**
     * @var DeliveryDateOptionsFactory
     */
    protected $deliveryDateOptionsFactory;

    public function __construct(
        CartItemExtensionFactory $cartItemExtensionFactory,
        DeliveryDateOptionsFactory $deliveryDateOptionsFactory
    ) {
        $this->cartItemExtensionFactory = $cartItemExtensionFactory;
        $this->deliveryDateOptionsFactory = $deliveryDateOptionsFactory;
    }

    /**
     * 
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ) {

        if ($quote->getItems()) {
            /** @var  \Magento\Quote\Model\Quote\Item $item */
            foreach ($quote->getItems() as $item) {

                $deliveryDateOptions = $this->getDeliveryDateOptionValues($item);
                if ($deliveryDateOptions->getOptionTitle() != null && $deliveryDateOptions->getOptionTitle() != null) {

                    $extensionAttributes = $item->getExtensionAttributes();
                    if ($extensionAttributes === null) {
                        $extensionAttributes = $this->cartItemExtensionFactory->create();
                    }
                    $extensionAttributes->setDeliveryDateOptions($deliveryDateOptions);
                    $item->setExtensionAttributes($extensionAttributes);
                }
            }
        }
        return $quote;
    }

    /**
     * get delivery date option values
     * @param \Magento\Quote\Model\Quote\Item  $item
     * @return \MMT\Cart\Api\Data\DeliveryDateOptionsInterface
     */
    public function getDeliveryDateOptionValues($item)
    {
        $deliveryDateOptionsFactory = $this->deliveryDateOptionsFactory->create();

        $options = $item->getOptions();
        foreach ($options as $optionProduct) {
            $product = $optionProduct->getProduct();

            $optionIds = $optionProduct->getProduct()->getCustomOption('option_ids');
            if ($optionIds) {
                foreach (explode(',', $optionIds->getValue()) as $optionId) {
                    // var_dump($optionId."Id");
                    $option = $product->getOptionById($optionId);

                    if ($option) {
                        $confItemOption = $product->getCustomOption('option_' . $option->getId());
                        $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setProduct($product)
                            ->setConfigurationItemOption($confItemOption);

                        $value = $group->getFormattedOptionValue($confItemOption->getValue());
                        $deliveryDateOptionsFactory->setOptionTitle($option->getTitle());
                        $deliveryDateOptionsFactory->setOptionValue($value);
                    }
                }
            }
        }
        return $deliveryDateOptionsFactory;
    }
}

