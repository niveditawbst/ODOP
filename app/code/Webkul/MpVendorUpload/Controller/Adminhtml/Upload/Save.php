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

namespace Webkul\MpVendorUpload\Controller\Adminhtml\Upload;

use Magento\Framework\App\Filesystem\DirectoryList;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

class Save extends \Webkul\MpVendorUpload\Controller\Adminhtml\Upload
{
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $xmlParser;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    protected $publisher;

    /**
     * @var \Magento\Eav\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Webkul\MpVendorUpload\Model\UploadProfileFactory
     */
    protected $uploadProfileFactory;

    /**
     * @var \Magento\MpVendorUpload\helper\Data
     */
    protected $mpVendorHelper;

    /**
     * @var MpSellerFactory
     */
    protected $mpSellerFactory;

   /**
    * @param \Magento\Backend\App\Action\Context $context
    * @param \Magento\Framework\File\Csv $csvProcessor
    * @param \Magento\Framework\Xml\Parser $xmlParser
    * @param \Magento\Framework\App\ResourceConnection $resource
    * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
    * @param \Magento\Eav\Model\AttributeFactory $attributeFactory
    * @param \Webkul\MpVendorUpload\Model\UploadProfileFactory $uploadProfileFactory
    * @param \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper
    * @param \Magento\Eav\Model\EntityFactory $entity
    * @param CollectionFactory $attributeCollectionFactory
    * @param \Webkul\MpVendorUpload\Logger\Logger $vendorlogger
    */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Xml\Parser $xmlParser,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\MessageQueue\PublisherInterface $publisher,
        \Magento\Eav\Model\AttributeFactory $attributeFactory,
        \Webkul\MpVendorUpload\Model\UploadProfileFactory $uploadProfileFactory,
        \Webkul\MpVendorUpload\Helper\Data $mpVendorHelper,
        \Magento\Eav\Model\EntityFactory $entity,
        CollectionFactory $attributeCollectionFactory,
        \Webkul\MpVendorUpload\Logger\Logger $vendorlogger
    ) {
        $this->csvProcessor                 = $csvProcessor;
        $this->xmlParser                    = $xmlParser;
        $this->connection                   = $resource->getConnection();
        $this->resource                     = $resource;
        $this->publisher                    = $publisher;
        $this->attributeFactory             = $attributeFactory;
        $this->uploadProfileFactory         = $uploadProfileFactory;
        $this->mpVendorHelper               = $mpVendorHelper;
        $this->entity                       = $entity;
        $this->attributeCollectionFactory   = $attributeCollectionFactory;
        $this->vendorlogger                 = $vendorlogger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $files = $this->getRequest()->getFiles();
        $noValidate="";
        
        if ($this->mpVendorHelper->getfileAndMedia()) {
            if ($files['massupload_zip_file']['name']=="") {
                $noValidate='image';
            }
            $validateData = $this->mpVendorHelper->validateUploadedFiles($noValidate);
            if ($validateData['error']) {
                $this->messageManager->addError(__($validateData['msg']));
                return $resultRedirect->setPath('*/*/', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        
        try {
            switch ($files['massupload_file']['type']) {
                case 'text/csv':
                    $data = $this->getCsvData($files['massupload_file']['tmp_name']);
                    break;
                case 'text/xml':
                    $data = $this->getXmlData($files['massupload_file']['tmp_name']);
                    break;
                case 'application/vnd.ms-excel':
                    $data = $this->getXlsData($files['massupload_file']['tmp_name']);
                    break;
                default:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Please upload a valid CSV/XML/XLS file")
                    );
            }
            // Validate data

            $this->validateData($data);

            //insert profiles into the queue

            list($data,$csv) = $this->emailShopUrlValidation($data);
            
            //data log
            $this->vendorlogger->info($this->mpVendorHelper->jsonEncodeData($csv));

            foreach ($data as $row) {
                $id[] = $this->insertRow($row, $data);
            }
            
            if (!empty($id)) {
                $this->publisher->publish('vendorupload.topic', $this->mpVendorHelper->jsonEncodeData($id));
                $this->messageManager->addSuccess(
                    __("Your upload requests have been successfully added to the queue")
                );

                // File and Media Upload
                if ($this->mpVendorHelper->getfileAndMedia() && empty($noValidate)) {
                    $uploadZip = $this->mpVendorHelper->uploadZip($data, $params);
                    if ($uploadZip['error']) {
                        $this->messageManager->addError(__($uploadZip['msg']));
                        return $resultRedirect->setPath('*/*/', ['_secure'=>$this->getRequest()->isSecure()]);
                    }
                }
            }

            if (isset($csv['error']) && !empty($csv['error'])) {
                $this->messageManager->addNotice(
                    __("%1 account already exits with email's or shopurl", $csv['error'])
                );
            }
        } catch (\Exception $e) {
            $this->vendorlogger->info($e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/', ['_secure'=>$this->getRequest()->isSecure()]);
    }

    /**
     * get data array from CSV
     * @param  string
     * @return array
     */
    protected function getCsvData($file)
    {
        $convertedData = $this->csvProcessor->getData($file);
        
        $returnArray = [];
        $keyArray = '';
        foreach ($convertedData as $index => $value) {
            if ($index==0) {
                $keyArray = $value;
            } else {
                foreach ($value as $key => $row) {
                    $returnArray[$index-1][$keyArray[$key]] = $row;
                }
            }
        }
        return $returnArray;
    }

    /**
     * get data array from XML
     * @param  string
     * @return array
     */
    protected function getXmlData($file)
    {
        $returnArray = $this->xmlParser->load($file)->xmlToArray();
        if (count($returnArray['node']['vendor'])!=count($returnArray['node']['vendor'], COUNT_RECURSIVE)) {
            return $returnArray['node']['vendor'];
        } else {
            return [$returnArray['node']['vendor']];
        }
    }

    /**
     * get data array from XLS
     * @param  string
     * @return array
     */
    protected function getXlsData($file)
    {
        $spreadsheet = IOFactory::load($file);
        $convertedData = $spreadsheet->getActiveSheet()->toArray();
        $returnArray = [];
        $keyArray = '';
        foreach ($convertedData as $index => $value) {
            if ($index==0) {
                $keyArray = $value;
            } else {
                if (!empty(array_filter($value))) {
                    foreach ($value as $key => $row) {
                        $returnArray[$index-1][$keyArray[$key]] = $row;
                    }
                }
            }
        }
        return $returnArray;
    }

    /**
     * Checking customer exists with email
     *
     * @param array $rowData
     * @return array
     */
    public function emailShopUrlValidation($rowData)
    {
        $data = [];
        $csv = [];
        $csv['error'] = false;
        $error = 0;
        foreach ($rowData as $key => $value) {
                $email = $this->mpVendorHelper->checkEmail($value['email'], $value['store_id']);
                $shopUrl = $this->mpVendorHelper->checkShopUrl($value['profileurl']);
            if ($email && $shopUrl) {
                $data[] = $value;
                $value['status'] = __("Uploaded");
                $csv['csv'][$key] = $value;
            } else {
                    $error++;
                    $value['status'] = __("Email or ShopUrl already exists");
                    $csv['csv'][$key] = $value;
                    $csv['error'] = $error;
                    
            }
        }
        return [$data,$csv];
    }

    /**
     * save vendor profile data
     * @param  array
     * @return int
     */
    protected function insertRow($row, $fileData)
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $options = [];
        /** Get Attribute Value */
        $entityTypeId = $this->entity->create()->setType('customer')->getTypeId();
        foreach ($row as $attrCode => $value) {
            if ($attrCode == 'group_name') {
                $attrCode = 'group_id';
            }
            $attributeCollection = $this->attributeCollectionFactory->create()->setEntityTypeFilter($entityTypeId)
                    ->addFieldToFilter('is_required', ['eq'=>'1'])
                    ->addFieldToFilter('attribute_code', ['eq'=>$attrCode]);
            
            foreach ($attributeCollection as $key => $vendorAttribute) {
                
                if (in_array($vendorAttribute->getFrontendInput(), ['select','multiselect'])
                && $attrCode!=='store_id') {
                    $row[$attrCode] = $vendorAttribute->getSource()->getOptionId($value);
                }
            }
        }
        
        //save profile
        $model = $this->uploadProfileFactory->create();
        
        $collection = $this->getVendorCollection($model, $params);
        try {
            $model->setLabel($params['profile_label']);
            $model->setProfileurl($row['profileurl']);
            $model->setEmail($row['email']);
            $model->setFirstname($row['firstname']);
            $model->setGroupId($row['group_id']);
            $model->setLastname($row['lastname']);
            $model->setStoreId($row['store_id']);
            $model->save();
        } catch (\Exception $e) {
            $this->vendorlogger->info($e->getMessage());
        }
            
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            //save vendor profile attributes
            $this->setVendorAttribute($collection, $model, $row);
        }
        return $model->getId();
    }

    /**
     * Save Additional Attribute in profile
     *
     * @param array $collection
     * @param array $model
     * @return void
     */
    public function setVendorAttribute(
        $collection,
        $model,
        $row
    ) {
        $insertArray = [];
        foreach ($collection as $value) {
            try {
                    $code = $this->attributeFactory->createAttribute(\Magento\Eav\Model\Attribute::class)
                                ->load($value->getAttributeId())->getAttributeCode();

                $attributeValue = $this->getAttributeValue($code, $value, $row);

                if (array_key_exists($code, $row)) {
                    $insertArray[] = [
                        'profile_id'=>$model->getId(),
                        'code'=>$code,
                        'value'=>$attributeValue,
                        'frontend_input'=>$value->getFrontendInput()
                    ];
                }
            } catch (\Exception $e) {
                $this->vendorlogger->info($e->getMessage());
            }
        }

        /** Save Vendor Attribute into table */
        try {
            $tableName = $this->resource->getTableName(\Webkul\MpVendorUpload\Model\ProfileAttributes::TABLE_NAME);
            $this->connection->beginTransaction();
            $this->connection->insertMultiple($tableName, $insertArray);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $this->vendorlogger->info($e->getMessage());
        }
    }

    /**
     * Get Vendor Attribute Collection
     *
     * @param array $model
     * @param integer $params
     * @return array
     */
    public function getVendorCollection(
        $model,
        $params
    ) {
        $collection = [];
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            $collection = $this->mpVendorHelper->vendorAttributeCollection();
            $helper = $this->mpVendorHelper->vendorAttributeHelper();
            if ($helper->getConfigData('group_display')) {
                $model->setVendorGroup($params['assign_group']);
                $collection = $this->mpVendorHelper->vendorGroupsAttributes($params['assign_group']);
            }
        }
        return $collection;
    }

    /**
     * Get Attribute OptionId by value
     *
     * @param  string $code
     * @param  array $value
     * @param  array $row
     * @return string
     */
    public function getAttributeValue(
        $code,
        $value,
        $row
    ) {
        $params = $this->getRequest()->getParams();
        $profileLabel = $params['profile_label'];
        $optionId = "";
        if ($value->getFrontendInput() == 'select') {
            $optionId = $value->getSource()->getOptionId(trim($row[$code]));
        } elseif ($value->getFrontendInput() == 'multiselect') {
            if (strpos($row[$code], ',') !== false) {
                $rowOptions = explode(",", $row[$code]);
                $optionData=[];
                foreach ($rowOptions as $option) {
                    $optionData[]= $value->getSource()->getOptionId(trim($option));
                }
                $optionId = $this->mpVendorHelper->jsonEncodeData($optionData, true);
            } else {
                $optionId = $value->getSource()->getOptionId(trim($row[$code]));
            }
            
        } elseif ($value->getFrontendInput() == 'image' || $value->getFrontendInput() == 'file') {
            $shopUrl = $row['profileurl'];
            $optionId = $shopUrl.'/'.$row[$code];
        } else {
            $optionId  =  $row[$code];
        }
        return $optionId;
    }

    /**
     * Check and validate the header of the field
     *
     * @param array $data
     * @return void
     */
    public function validateData($data)
    {
        $vendorAttributeCode = [];
        $requiredKeys = [
            'email',
            'firstname',
            'group_name',
            'lastname',
            'store_id',
            'profileurl'
        ];
        
        if ($this->mpVendorHelper->vendorAttributeModule()) {
            $vendorAttributeCode = $this->mpVendorHelper->vendorAttributeCode();
        }

        foreach ($data[0] as $key => $val) {
            if (!in_array($key, $requiredKeys) && (!in_array($key, $vendorAttributeCode))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Something wrong with the uploaded file format")
                );
            }
        }
    }
}
