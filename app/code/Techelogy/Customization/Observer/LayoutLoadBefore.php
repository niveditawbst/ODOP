<?php
namespace Techelogy\Customization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class LayoutLoadBefore implements ObserverInterface
{
     /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    public function __construct(
       \Magento\Framework\Registry $registry
    )
    {
        $this->_registry = $registry;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->_registry->registry('current_product');
        if(!$product){
			return $this;
        }
        if($product->getData('exhibition_product') ==1){ // your condition
           $layout = $observer->getLayout();
           $layout->getUpdate()->addHandle('catalog_product_view_exhibition_product_1');
        }
        return $this;
    }
}
?>
