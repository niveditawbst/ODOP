<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorUpload\Block\Adminhtml;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

class Column extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $entity;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $inputTypeFactory;

    /**
     * @var \Magento\MpVendorUpload\helper\Data
     */
    protected $mpVendorHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context                              $context
     * @param \Magento\Eav\Model\EntityFactory                                    $entity
     * @param CollectionFactory                                                   $attributeCollectionFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory  $inputTypeFactory
     * @param \Webkul\MpVendorUpload\helper\Data                                  $mpVendorHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface                         $storeRepository
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Eav\Model\EntityFactory $entity,
        CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        array $data = []
    ) {
        $this->entity                     = $entity;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->inputTypeFactory           = $inputTypeFactory;
        $this->mpVendorHelper             = $mpVendorHelper;
        $this->storeRepository            = $storeRepository;
        parent::__construct($context, $data);
    }

    /**
     * get required vendor attributes
     * @return array
     */
    public function getCustomerAttributes()
    {
        $entityTypeId = $this->entity->create()->setType('customer')->getTypeId();

        $vendorAttributeIds = [];
        $requiredArray = [];
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            $collection = $this->mpVendorHelper->vendorAttributeCollection();
            if ($collection->getSize()) {
                foreach ($collection as $attribute) {
                    array_push($vendorAttributeIds, $attribute->getAttributeId());
                    $requiredArray[$attribute->getAttributeId()] = $this->mpVendorHelper->isAttributeRequired(
                        $attribute->getAttributeId()
                    );
                }
            }

        }

        $attributeCollection = $this->attributeCollectionFactory->create()->setEntityTypeFilter($entityTypeId)
                    ->addFieldToFilter(
                        ['is_required','main_table.attribute_id'],
                        [['eq'=>'1'],['in'=>$vendorAttributeIds]]
                    );
        $i = 0;
        //create return array
        $returnArray[] = [
            "id"=>$i++,
            "code"=>'no-data',
            "label"=>__('Please Select'),
            "frontend_input"=>'no-data',
            "is_required"=>0,
            "options"=>[]
        ];
        
        foreach ($attributeCollection as $key => $vendorAttribute) {
            $options = [];
            if (in_array($vendorAttribute->getFrontendInput(), ['select','multiselect'])) {
                $options = $vendorAttribute->getSource()->getAllOptions();
                if ($vendorAttribute->getAttributeCode() == 'store_id') {
                    $options = $this->getAllStore();
                } else {
                    $dataOptions = [];
                    foreach ($options as $option) {
                        $dataOptions[] = ['value'=>$option['label'],'label'=>$option['label']];
                    }
                    $options = $dataOptions;
                }
            }
            if (in_array($key, $vendorAttributeIds)) {
                $required = $requiredArray[$key];
            } else {
                $required = $vendorAttribute->getIsRequired();
            }
            if ($vendorAttribute->getAttributeCode() == 'group_id') {
                $attributeCode = 'group_name';
                
            } else {
                $attributeCode = $vendorAttribute->getAttributeCode();
            }
            
            if ($vendorAttribute->getAttributeCode() != 'website_id') {
                $returnArray[] = [
                    "id"=>$i++,
                    "code"=>$attributeCode,
                    "label"=>$vendorAttribute->getFrontendLabel(),
                    "frontend_input"=>$this->getInputTypeLabel($vendorAttribute->getFrontendInput()),
                    "is_required"=>$required,
                    "options"=>$options
                ];
            }
        }
        return $returnArray;
    }

    /**
     * get label from input type code
     * @param  string
     * @return string
     */
    protected function getInputTypeLabel($code)
    {
        $attributeTypes = $this->inputTypeFactory->create()->toOptionArray();
        $additionalTypes = [
            ['value' => 'image', 'label' => __('Media Image')],
            ['value' => 'file', 'label' => __('File')],
        ];
        $attributeTypes = array_merge($attributeTypes, $additionalTypes);
        foreach ($attributeTypes as $type) {
            if ($type['value']==$code) {
                return $type['label'];
            }
        }
    }

    /**
     * GetAll Store Value And Id
     *
     * @return array
     */
    public function getAllStore()
    {
        $storeOptions = [];
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeOptions[] = ['value'=>$store->getId(),'label'=>$store->getName()];
        }
        return $storeOptions;
    }
}
