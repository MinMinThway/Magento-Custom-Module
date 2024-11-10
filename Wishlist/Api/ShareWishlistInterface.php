<?php
/**
 * A Magento 2 module named MMT/Wishlist
 *
 */
namespace MMT\Wishlist\Api;

/**
 * Interface ShareWishlistInterface
 * @api
 */
interface ShareWishlistInterface
{
    /**
     * Share wishlist
     * @param string email
     * @param string message
     * @param string customerId
     * @return boolean
     */
    public function shareWishList(array $email, $message, $customerId);

}
