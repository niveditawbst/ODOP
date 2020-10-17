<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorUpload\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class QtySold.
 */
class VendorGroups extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Webkul\MpVendorUpload\Helper\Data
     */
    protected $mpVendorHelper;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param  \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper,
        array $components = [],
        array $data = []
    ) {
        $this->mpVendorHelper = $mpVendorHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            if (isset($dataSource['data']['items'])) {
                $fieldName = $this->getData('name');
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$fieldName] = $this->mpVendorHelper->getVendorGroupsData(
                        $item['vendor_group']
                    )->getGroupName();
                }
            }
        }
        return $dataSource;
    }

     /**
      * column Enable When vendor Attribute Enable
      *
      * @return boolean
      */
    public function prepare()
    {
        parent::prepare();
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            $this->_data['config']['componentDisabled'] = false;
        }
    }
}
