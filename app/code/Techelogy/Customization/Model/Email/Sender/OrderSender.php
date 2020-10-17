<?php
namespace Techelogy\Customization\Model\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
   protected function prepareTemplate(Order $order)
   {
       parent::prepareTemplate($order);

       //check if only exhibition product
       $onlyExhibitionProduct = 1;
       $templateId = $order->getCustomerIsGuest() ? $this->identityContainer->getGuestTemplateId() : $this->identityContainer->getTemplateId();
       $items = $order->getAllVisibleItems();
       foreach ($items as $item) {
          $isExhibition = $item->getProduct()->getData('exhibition_product');
          if(!$isExhibition)
          {
              $onlyExhibitionProduct = 0;
              break;
          }
       }
       if($onlyExhibitionProduct)
       {
            $templateId = 4;
       }
       $this->templateContainer->setTemplateId($templateId);
   }

 }