<?php
namespace MMT\Checkout\Api\Data;

interface CustomShippingAndPaymentMethodInterface
{
  const PAYMENT_METHODS = 'payment_methods';
  const SHIPPING_METHODS = 'shipping_methods';

  /**
   * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
   */
  public function getPaymentMethods();

  /**
   * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $paymentMethods
   * @return $this
   */
  public function setPaymentMethods($paymentMethods);


  /**
   * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
   */
  public function getShippingMethods();

  /**
   * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $shippingMethods
   * @return $this
   */
  public function setShippingMethods($shippingMethods);

}