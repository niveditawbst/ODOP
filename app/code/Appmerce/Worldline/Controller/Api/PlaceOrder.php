<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Controller\Api;

class PlaceOrder extends \Appmerce\Worldline\Controller\Worldline
{
    /**
     * Return JSON form fields
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $response = FALSE;
        
        $incrementId = $this->checkoutSession->getLastRealOrder()->getIncrementId();
        $this->log->addDebug($incrementId);
        if ($order = $this->_getOrder()->loadByIncrementId($incrementId)) {
            $response = $this->getJsonData($order);
        }
        
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
