<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Controller\Push;

class Response extends \Appmerce\Worldline\Controller\Worldline
{
    /**
     * Cancel payment
     *
     * @return void
     */
    public function execute()
    {
        $this->log->addDebug(__('Processing Worldline response...'));

        $params = $this->getRequest()->getParams();
        $this->log->addDebug(json_encode((array)$params));

        if (isset($params['Data']) && isset($params['Seal'])) {
            $data = $this->getDataBits($params['Data']);
            $transactionId = $data['orderId'];
            $order = $this->_getOrder()->loadByIncrementId($transactionId);

            if ($this->checkSeal($params, $order)) {
                $responseMessage = $this->getResponseText($data['responseCode']);
                $note = __('Response code %1: %2.', $data['responseCode'], $responseMessage);

                switch ($data['responseCode']) {
                    case self::STATUS_SUCCESS :
                        $this->processSuccess($order, $note, $transactionId);
                        $this->log->addDebug(__('Payment complete.'));
                        break;

                    case self::STATUS_PENDING :
                        $this->processPending($order, $note, $transactionId);
                        $this->log->addDebug(__('Pending payment.'));
                        break;

                    case self::STATUS_TIMEOUT :
                    default :
                        $this->processCancel($order, $transactionId);
                        $this->log->addDebug(__('Payment failed.'));
                }
            }
        }

        // Worldline is known to mess up the postback (especially for iDEAL)
        // So we add this block for such cases...
        // It's not correct, but Worldline is still not very stable...
        elseif (isset($params['orderId'])) {
            $transactionId = $data['orderId'];
            $order = $this->_getOrder()->loadByIncrementId($transactionId);
            $responseMessage = $this->getResponseText($params['responseCode']);
            $note = __('Response code %1: %2.', $params['responseCode'], $responseMessage);

            switch ($params['responseCode']) {
                case self::STATUS_SUCCESS :
                    $this->processSuccess($order, $note, $transactionId);
                    $this->log->addDebug(__('Payment complete.'));
                    break;

                case self::STATUS_PENDING :
                    $this->processPending($order, $note, $transactionId);
                    $this->log->addDebug(__('Pending payment.'));
                    break;

                case self::STATUS_TIMEOUT :
                default :
                    $this->processCancel($order, $transactionId);
                    $this->log->addDebug(__('Payment failed.'));
            }
        }
        
        // Return HTTP OK
        http_response_code(200);
        return;
    }
}
