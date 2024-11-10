<?php

namespace MMT\PayNow\Api;

interface PayNowInterface
{

    /**
     * get kpay credential for mobile
     * @param int $id
     * @return \MMT\PayNow\Api\Data\KPayCredentialInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getKPayCredentialForMobile($id);
}
