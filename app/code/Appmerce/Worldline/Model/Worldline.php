<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Model;

class Worldline extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_WORLDLINE_CODE = 'appmerce_worldline';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_WORLDLINE_CODE;
    
    /**
     * @var boolean
     */
    protected $_canRefund = false;

    /**
     * @var boolean
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * @var boolean
     */
    protected $_canUseInternal = false;
}
