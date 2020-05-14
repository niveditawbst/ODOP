<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Model\Api;

class Ideal extends \Appmerce\Worldline\Model\Worldline
{
    const PAYMENT_METHOD_WORLDLINE_IDEAL_CODE = 'appmerce_worldline_ideal';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_WORLDLINE_IDEAL_CODE;
}
