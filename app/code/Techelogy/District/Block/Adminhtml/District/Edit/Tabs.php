<?php
namespace Techelogy\District\Block\Adminhtml\District\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('checkmodule_district_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('District Information'));
    }
}