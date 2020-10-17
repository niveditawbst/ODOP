<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorUpload\Block\Adminhtml\Upload\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendor_upload_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor Mass Upload'));
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            "vendor_upload",
            [
                "label"     =>  __("Vendor Upload"),
                "alt"       =>  __("Vendor Upload"),
                "content"   =>  $this->getLayout()
                ->createBlock(\Webkul\MpVendorUpload\Block\Adminhtml\Upload\Edit\Tab\Main::class, "main")
                ->toHtml()
            ]
        );
        $this->addTab(
            "columns",
            [
                "label"     =>  __("Column Attributes"),
                "alt"       =>  __("Column Attributes"),
                "content"   =>  $this->getLayout()
                ->createBlock(\Webkul\MpVendorUpload\Block\Adminhtml\Column::class, "vendor.upload.column")
                ->setTemplate("Webkul_MpVendorUpload::column.phtml")->toHtml()
            ]
        );
        return parent::_prepareLayout();
    }
}
