<?php
 /**
  * @category  Webkul
  * @package   Webkul_MpVendorUpload
  * @author    Webkul
  * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
  * @license   https://store.webkul.com/license.html
  */

namespace Webkul\MpVendorUpload\Block\Adminhtml\Upload;

class Upload extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpVendorUpload';
        $this->_controller = 'adminhtml_upload';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Upload'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
    }
}
