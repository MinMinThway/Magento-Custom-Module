<?php

namespace MMT\Cart\Api;

interface CustomCartTotalRepositoryInterface
{

    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \MMT\Cart\Api\Data\CustomTotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * Returns quote totals data for a specified cart for guest.
     *
     * @param string $cartId The cart ID.
     * @return \MMT\Cart\Api\Data\CustomTotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getTotalForGuest($cartId);

    /**
     * Returns quote total items count
     * 
     * @param int $cartId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getCartTotalItemsCount($cartId);

    /**
     * Returns quote total items count for guest
     * 
     * @param string $cartId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getCartTotalItemsCountGuest($cartId);
}
