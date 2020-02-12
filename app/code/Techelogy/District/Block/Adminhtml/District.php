<?php
namespace Techelogy\District\Block\Adminhtml;
class District extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_district';/*block grid.php directory*/
        $this->_blockGroup = 'Techelogy_District';
        $this->_headerText = __('District');
        $this->_addButtonLabel = __('Add New Entry'); 
        parent::_construct();
		
    }
}
