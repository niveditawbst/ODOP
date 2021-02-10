<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Techelogy\Customization\Ui\DataProvider\Product;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    /**
     * For filter grid according to category
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'source_id' && $filter->getValue()) {
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$sourceItems = $objectManager->create('\Magento\Inventory\Model\SourceItemFactory')->create()->getCollection()
							->addFieldToFilter('source_code', $filter->getValue());
			
			$sourceItemSkus = [];
			foreach($sourceItems as $sourceItem){
				$sourceItemSkus[] = $sourceItem->getSku();
			}

            $this->getCollection()->addAttributeToFilter('sku', ['in' => $sourceItemSkus]);
        //~ }elseif (isset($this->addFilterStrategies[$filter->getField()])) {
            //~ $this->addFilterStrategies[$filter->getField()]
                //~ ->addFilter(
                    //~ $this->getCollection(),
                    //~ $filter->getField(),
                    //~ [$filter->getConditionType() => $filter->getValue()]
                //~ );
        } else {
            parent::addFilter($filter);
        }
    }
}
