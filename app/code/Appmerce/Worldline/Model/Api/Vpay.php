<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Model\Api;

class Vpay extends \Appmerce\Worldline\Model\Worldline
{
    const PAYMENT_METHOD_WORLDLINE_VPAY_CODE = 'appmerce_worldline_vpay';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_WORLDLINE_VPAY_CODE;
}
