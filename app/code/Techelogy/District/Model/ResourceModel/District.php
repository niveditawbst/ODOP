<?php
/**
 * Copyright © 2015 Techelogy. All rights reserved.
 */
namespace Techelogy\District\Model\ResourceModel;

/**
 * District resource
 */
class District extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('district_district', 'id');
    }

  
}
