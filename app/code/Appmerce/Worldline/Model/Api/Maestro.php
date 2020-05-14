<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Model\Api;

class Maestro extends \Appmerce\Worldline\Model\Worldline
{
    const PAYMENT_METHOD_WORLDLINE_MAESTRO_CODE = 'appmerce_worldline_maestro';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_WORLDLINE_MAESTRO_CODE;
}
