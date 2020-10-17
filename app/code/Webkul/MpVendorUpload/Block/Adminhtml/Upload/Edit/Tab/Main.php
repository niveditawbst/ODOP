<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorUpload\Block\Adminhtml\Upload\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\UrlInterface;

class Main extends Generic implements TabInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MpVendorUpload\helper\Data
     */
    protected $mpVendorHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context       $context
     * @param \Magento\Framework\Registry                   $registry
     * @param \Magento\Framework\Data\FormFactory           $formFactory
     * @param \Magento\Store\Model\StoreManagerInterface    $storeManager
     * @param \Webkul\MpVendorUpload\helper\Data            $mpVendorHelper
     * @param array                                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper,
        array $data = []
    ) {
        $this->mediaDirectory = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $this->mpVendorHelper             = $mpVendorHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var DataForm $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' =>$this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );
        $form->setHtmlIdPrefix('wk_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Profile Information'),
                'class' => 'fieldset-wide'
            ]
        );
        $fieldset->addField(
            'profile_label',
            'text',
            [
                'name' => 'profile_label',
                'label' => __('Profile Label'),
                'title' => __('Profile Label'),
                'required' => true
            ]
        );
        if ($this->mpVendorHelper->getConfigData('group_display')) {
            $fieldset->addField(
                'assign_group',
                'select',
                [
                    'name' => 'assign_group',
                    'label' => __('Assign Group'),
                    'title' => __('Assign Group'),
                    'values' => $this->mpVendorHelper->vendorGroupsOption(),
                    'required' => true,
                    'class' => 'assign_group'
                ]
            );
        }
        $fieldset->addField(
            'massupload_file',
            'file',
            [
                'name' => 'massupload_file',
                'label' => __('Upload CSV/XML/XLS'),
                'title' => __('Upload CSV/XML/XLS'),
                'required' => true
            ]
        );
        
        if ($this->mpVendorHelper->getfileAndMedia()) {
            $fieldset->addField(
                'massupload_zip_file',
                'file',
                [
                    'name' => 'massupload_zip_file',
                    'label' => __('Upload Image/file Zip'),
                    'title' => __('Upload Image/file Zip')
                ]
            );
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare form Html. call the phtml file with form.
     *
     * @return string
     */
    public function getFormHtml()
    {
       // get the current form as html content.
        $html = parent::getFormHtml();
        //Append the phtml file before the form content.
        return $this->setTemplate('Webkul_MpVendorUpload::link.phtml')->toHtml().$html;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Vendor Upload');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Vendor Upload');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * get link for sample CSV file
     * @return string
     */
    public function getCsvLink()
    {
        $url = $this->mediaDirectory . 'marketplace/vendorupload/';
        $fileName = $this->getSamplefileName();
        return $url . $fileName.'.csv';
    }

    /**
     * get link for sample XML file
     * @return string
     */
    public function getXmlLink()
    {
        $url = $this->mediaDirectory . 'marketplace/vendorupload/';
        $fileName = $this->getSamplefileName();
        return $url . $fileName.'.xml';
    }

    /**
     * get link for sample XLS file
     * @return string
     */
    public function getXlsLink()
    {
        $url = $this->mediaDirectory . 'marketplace/vendorupload/';
        $fileName = $this->getSamplefileName();
        return $url . $fileName.'.xls';
    }

    public function getSamplefileName()
    {
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            return 'sample_vendorattribute';
        }
        return 'sample';
    }
}
