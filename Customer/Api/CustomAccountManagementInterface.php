<?php

namespace MMT\Customer\Api;

interface CustomAccountManagementInterface
{

    /**
     * customize reset password for mobile
     *
     * @param string $email
     * @param string $template
     * @param int $websiteId
     * @return \MMT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);

    /**
     * get create account config for required and show/hide field
     * @return \MMT\Customer\Api\Data\AccountConfigInterface
     */
    public function getAccountConfig();

    /**
     * activate customer account by otp code
     * @param string $email
     * @param string $otpCode
     * @return \MMT\Product\Api\Data\StatusShowInterface
     */
    public function activateAccountByOtpCode($email, $otpCode);

    /**
     * resend confirmation email with otp
     * @param string $email
     * @param int $websiteId
     * @return \MMT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendConfirmEmailWithOtp($email, $websiteId = null);

    /**
     * Reset customer password.
     *
     * @param string $email If empty value given then the customer
     * will be matched by the RP token.
     * @param string $resetToken
     * @param string $newPassword
     *
     * @return \MMT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws InputException
     */
    public function resetPassword($email, $resetToken, $newPassword);

    /**
     * customize resend otp code
     * 
     * @param string $email
     * @param int $websiteId
     * @return \MMT\Product\Api\Data\StatusShowInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPasswordSendOtp($email,$websiteId=null);

    /**
     * customer profile photo upload
     * @param \MMT\Customer\Api\Data\ProfilePictureInterface $files
     * @param string $customerId
     * @return \MMT\Product\Api\Data\StatusShowInterface;
     */
    public function save($files, $customerId);

    /**
     * how to create customer account video
     * @param int $type
     * @return \MMT\Product\Api\Data\StatusShowInterface
     */
    public function howToCreateAccountVideo($type);

    /**
     * delete self account request
     * 
     * @param string $email
     * @return bool
     */
    public function deleteSelf($email);
}
