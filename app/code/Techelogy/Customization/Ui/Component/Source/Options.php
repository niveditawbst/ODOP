<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Techelogy\Customization\Ui\Component\Source;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @var array
     */
    protected $options;
    protected $sourceFactory;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(
		SystemStore $systemStore, 
		Escaper $escaper,
		\Magento\Inventory\Model\SourceFactory $sourceFactory
	)
    {
        $this->systemStore = $systemStore;
        $this->escaper = $escaper;
        $this->sourceFactory = $sourceFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->getSourceList();

        $this->options = $this->getSourceList();

        return $this->options;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function getSourceList(){
		$sourceCollection = $this->sourceFactory->create()->getCollection()->addFieldToFilter('enabled', 1);
		$sourceList = [];
		$sourceAllList = [];
		
		foreach($sourceCollection as $sourceItemName){
			$sourceList['value'] = $sourceItemName->getSourceCode();
			$sourceList['label'] = $sourceItemName->getName();

			$sourceAllList[] = $sourceList;
		}
		return $sourceAllList;

	}
}
