<?php
/**
 * Copyright © 2019 Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 */
namespace Appmerce\Worldline\Model\Api;

class Bcmc extends \Appmerce\Worldline\Model\Worldline
{
    const PAYMENT_METHOD_WORLDLINE_BCMC_CODE = 'appmerce_worldline_bcmc';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_WORLDLINE_BCMC_CODE;
}
