<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Techelogy\Customization\Model\Config\Source;

/**
 * Options provider for countries list
 *
 * @api
 * @since 100.0.2
 */
class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Countries
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_countryCollection;

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct(\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection)
    {
        $this->_countryCollection = $countryCollection;
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray($isMultiselect = false, $foregroundCountries = '')
    {
		$countries = [];
		$this->_options = [];

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$countryCollection = $objectManager->create('Magento\Directory\Helper\Data')->getCountryCollection();
		
		foreach ($countryCollection as $country) {
            $this->_options[] = [
                'value' => $country->getId(),
                'label' => $country->getName()
            ];
        }
		

        $options = $this->_options;

        return $options;
    }
}
