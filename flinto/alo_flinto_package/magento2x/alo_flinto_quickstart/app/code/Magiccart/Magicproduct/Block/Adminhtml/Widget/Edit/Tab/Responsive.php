<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2017-09-01 16:46:31
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Adminhtml\Widget\Edit\Tab;

use Magiccart\Magicproduct\Model\Status;
use Magiccart\Magicproduct\Model\System\Config\Col;

class Responsive extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $_objectFactory;
    protected $_col;

    /**
     * @var \Magiccart\Magicproduct\Model\Magicproduct
     */

    protected $_magicproduct;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magiccart\Magicproduct\Model\Magicproduct $magicproduct,
        Col $col,
        array $data = []
    ) {
        $this->_objectFactory = $objectFactory;
        $this->_magicproduct = $magicproduct;
        $this->_col = $col;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * prepare layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        return $this;
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('magicproduct');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('magic_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Responsive Tab Information')]);

        if ($model->getId()) {
            $fieldset->addField('magicproduct_id', 'hidden', ['name' => 'magicproduct_id']);
        }

        $fieldset->addField('mobile', 'select',
            [
                'label' => __('max-width 360px:'),
                'title' => __('Display in Screen <= 360:'),
                'name' => 'mobile',
                'options' => $this->_col->toOptionArray(),
                'value' => 1,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 1px) and (max-width: 360px)') .'</small></p>',
            ]
        );

        $fieldset->addField('portrait', 'select',
            [
                'label' => __('max-width 480px:'),
                'title' => __('Display in Screen 480:'),
                'name' => 'portrait',
                'options' => $this->_col->toOptionArray(),
                'value' => 2,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 361px) and (max-width: 480px)') .'</small></p>',
            ]
        );

        $fieldset->addField('landscape', 'select',
            [
                'label' => __('max-width 640px:'),
                'title' => __('Display in Screen 640:'),
                'name' => 'landscape',
                'options' => $this->_col->toOptionArray(),
                'value' => 3,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 481px) and (max-width: 640px)') .'</small></p>',
            ]
        );

        $fieldset->addField('tablet', 'select',
            [
                'label' => __('max-width 767px:'),
                'title' => __('Display in Screen 767:'),
                'name' => 'tablet',
                'options' => $this->_col->toOptionArray(),
                'value' => 3,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 641px) and (max-width: 767px)') .'</small></p>',
            ]
        );

        $fieldset->addField('notebook', 'select',
            [
                'label' => __('max-width 991px:'),
                'title' => __('Display in Screen 991px:'),
                'name' => 'notebook',
                'options' => $this->_col->toOptionArray(),
                'value' => 4,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 768px) and (max-width: 991px)') .'</small></p>',
            ]
        );

        $fieldset->addField('laptop', 'select',
            [
                'label' => __('max-width 1199px:'),
                'title' => __('Display in Screen 1199:'),
                'name' => 'laptop',
                'options' => $this->_col->toOptionArray(),
                'value' => 4,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 992px) and (max-width: 1199px)') .'</small></p>',
            ]
        );
		
        $fieldset->addField('desktop', 'select',
            [
                'label' => __('max-width 1919px:'),
                'title' => __('Display in Screen 1919:'),
                'name' => 'desktop',
                'options' => $this->_col->toOptionArray(),
                'value' => 4,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 1200px) and (max-width: 1919px)') .'</small></p>',
            ]
        );

        $fieldset->addField('visible', 'select',
            [
                'label' => __('min-width 1920px:'),
                'title' => __('Display Visible Items:'),
                'name' => 'visible',
                'options' => $this->_col->toOptionArray(),
                'value' => 4,
                'after_element_html' => '<p class="nm"><small>' . __('(min-width: 1920px)') .'</small></p>',
            ]
        );

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getMagicproduct()
    {
        return $this->_coreRegistry->registry('magicproduct');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getMagicproduct()->getId()
            ? __("Edit Tabs '%1'", $this->escapeHtml($this->getMagicproduct()->getTitle())) : __('New Tabs');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Responsive Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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
}
