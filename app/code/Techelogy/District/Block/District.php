<?php
/**
 * Copyright © 2015 Techelogy . All rights reserved.
 */
namespace Techelogy\District\Block;
use Magento\Framework\UrlFactory;
class District extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Techelogy\District\Helper\Data
     */
	 protected $_devToolHelper;
	 
	 /**
     * @var \Magento\Framework\Url
     */
	 protected $_urlApp;
	 
	 /**
     * @var \Techelogy\District\Model\Config
     */
    protected $_config;

    /**
     * @param \Techelogy\District\Block\Context $context
	 * @param \Magento\Framework\UrlFactory $urlFactory
     */
    public function __construct( \Techelogy\District\Block\Context $context
	)
    {
        $this->_devToolHelper = $context->getDistrictHelper();
		$this->_config = $context->getConfig();
        $this->_urlApp=$context->getUrlFactory()->create();
		parent::__construct($context);
	
    }
	
	/**
	 * Function for getting event details
	 * @return array
	 */
    public function getAvailableDistrict()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$district = $objectManager->create('Techelogy\District\Model\District')->getCollection();
		return $district;
    }	
    
    public function getCmsPages()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cmsPage = $objectManager->create('Magento\Cms\Model\Page')->getCollection()->addFieldToFilter('is_active', 1)->setOrder('sort_order', 'ASC');
		return $cmsPage;
    }	
    
    public function getProductsByTag($tagName){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$tagProducts = $objectManager->create('Magento\Catalog\Model\Product')->getCollection()
			->addAttributeToFilter('status', 1)
			->addAttributeToFilter('tags', ['like' => '%'.$tagName.'%']);
		return $tagProducts;
    }	
}
