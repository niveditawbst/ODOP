<?php
/**
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorUpload\Controller\Adminhtml;

abstract class Upload extends \Magento\Backend\App\AbstractAction
{
    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Webkul_Marketplace::marketplace');
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorUpload::massupload');
    }
}
