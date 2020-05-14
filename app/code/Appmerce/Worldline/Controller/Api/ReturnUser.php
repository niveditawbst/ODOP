<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Controller\Api;

class ReturnUser extends \Appmerce\Worldline\Controller\Worldline
{
    /**
     * Success payment
     *
     * @return void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->log->addDebug(json_encode((array)$params));

        $fail = true;
        if (isset($params['Data']) && isset($params['Seal'])) {
            $data = $this->getDataBits($params['Data']);
            $order = $this->_getOrder()->loadByIncrementId($data['orderId']);

            if ($this->checkSeal($params, $order)) {
                switch ($data['responseCode']) {
                    case self::STATUS_SUCCESS :
                    case self::STATUS_PENDING :
                        $fail = false;
                        $this->success();
                        break;
                }
            }
        }
        
        if ($fail == true) {
            $this->failure();
        }
    }

    /**
     * Success response
     */
    public function success()
    {
        // Processed by push notification
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

    /**
     * Failure response
     */
    public function failure()
    {
        // Canceled by push notification
        $this->restoreCart();
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

}
