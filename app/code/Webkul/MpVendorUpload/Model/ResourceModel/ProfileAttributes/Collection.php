<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorUpload\Model\ResourceModel\ProfileAttributes;

use Webkul\MpVendorUpload\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpVendorUpload\Model\ProfileAttributes::class,
            \Webkul\MpVendorUpload\Model\ResourceModel\ProfileAttributes::class
        );
    }
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
