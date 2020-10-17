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

namespace Webkul\MpVendorUpload\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Webkul\Marketplace\Model\SellerFactory as MpSellerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Data extends AbstractHelper
{
     /**
      * @var \Magento\Framework\Module\Manager
      */
    protected $moduleManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Eav\Model\EntityFactory $eavEntity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\MpVendorUpload\Model\UploadProfileFactory
     */
    protected $uploadProfileFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploader;

    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Webkul\MpVendorUpload\Logger\Logger
     */
    protected $vendorlogger;

    /**
     * @var \Webkul\MpVendorUpload\Model\Zip
     */
    protected $zip;

    /**
     * @var File
     */
    protected $file;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\EntityFactory $eavEntity
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MpVendorUpload\Model\UploadProfileFactory $uploadProfileFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param MpSellerFactory $mpSellerFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param DirectoryList $directoryList
     * @param \Webkul\MpVendorUpload\Logger\Logger $vendorlogger
     * @param \Webkul\MpVendorUpload\Model\Zip $zip
     * @param File $file
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\EntityFactory $eavEntity,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpVendorUpload\Model\UploadProfileFactory $uploadProfileFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        MpSellerFactory $mpSellerFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        DirectoryList $directoryList,
        \Webkul\MpVendorUpload\Logger\Logger $vendorlogger,
        \Webkul\MpVendorUpload\Model\Zip $zip,
        File $file
    ) {
        $this->moduleManager        = $moduleManager;
        $this->attributeCollection  = $attributeCollection;
        $this->eavEntity            = $eavEntity;
        $this->jsonHelper           = $jsonHelper;
        $this->uploadProfileFactory = $uploadProfileFactory;
        $this->customerFactory      = $customerFactory;
        $this->mpSellerFactory      = $mpSellerFactory;
        $this->fileUploader         = $fileUploaderFactory;
        $this->filesystem           = $filesystem;
        $this->fileDriver           = $fileDriver;
        $this->directoryList        = $directoryList;
        $this->vendorlogger         = $vendorlogger;
        $this->zip                  = $zip;
        $this->file                 = $file;
        parent::__construct($context);
    }

    /**
     * get module status
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'marketplace/vendor_upload/enable_disable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get Vendor Attribute module status
     *
     * @return boolean
     */
    public function vendorAttributeModule()
    {
        if ($this->moduleManager->isOutputEnabled('Webkul_MpVendorAttributeManager')) {
            return true;
        }
        return false;
    }

    /**
     * Get Vendor Attribute Collection
     *
     * @return array
     */
    public function vendorAttributeCollection()
    {
        $attributeUsedForCustomer = [0,1];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $vendorAttributeFactory = $objectManager->create(
            \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory::class
        );

        $collection = $vendorAttributeFactory->create()->getCollection()
                            ->addFieldToFilter('wk_attribute_status', ['eq'=>1])
                            ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsedForCustomer]);
        
            $attributeIds = $collection->getColumnValues('attribute_id');
            
            $typeId = $this->eavEntity->create()->setType('customer')->getTypeId();
            $collection = $this->attributeCollection->create()
                                       ->setEntityTypeFilter($typeId)
                                       ->addFilterToMap("attribute_id", "main_table.attribute_id")
                                       ->addFieldToFilter("attribute_id", ["in" => $attributeIds])
                                       ->setOrder('sort_order', 'ASC');
            return $collection;
    }

    /**
     * Check if attribute is required or not
     *
     * @param Int $attributeId
     *
     * @return Bool true|false
     */
    public function isAttributeRequired($attributeId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $vendorAttributeFactory = $objectManager->create(
            \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory::class
        );
        $customAttribute = $vendorAttributeFactory->create()->load($attributeId, "attribute_id");
        if ($customAttribute) {
            return $customAttribute->getRequiredField();
        }
        return false;
    }

    /**
     * Vendor Atrribute Helper Object
     *
     * @return object
     */
    public function vendorAttributeHelper()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(
            \Webkul\MpVendorAttributeManager\Helper\Data::class
        );
        return $helper;
    }

    /**
     * Vendor Attruibute Options
     *
     * @return array
     */
    public function vendorGroupsOption()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $vendorGroups = $objectManager->get(
            \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\VendorGroups::class
        );

        return $vendorGroups->toOptionArray();
    }

    /**
     * Get Field Config of vendor's Attribute
     *
     * @param string $data
     * @return boolean
     */
    public function getConfigData($data)
    {
        if ($this->vendorAttributeModule()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $vendorAttributeHelper = $objectManager->get(
                \Webkul\MpVendorAttributeManager\Helper\Data::class
            );
        
            return $vendorAttributeHelper->getConfigData($data);
        }
        return false;
    }

    /**
     * Get Vendor GroupWise Data Collection
     *
     * @param integer $id
     * @return array
     */
    public function getVendorGroupsData($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $vendorGroups = $objectManager->create(
            \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory::class
        );
        $availableGroups = $vendorGroups->create()->load($id);
        return $availableGroups;
    }

    /**
     * Get Vendor Group Attribute Collection
     *
     * @param interger $groupId
     * @return array
     */
    public function vendorGroupsAttributes($groupId = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $vendorAssignGroupFactory = $objectManager->create(
            \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory::class
        );
        $groupAttributes = $vendorAssignGroupFactory->create()->getCollection()
                                ->getGroupAttributes($groupId);
        return $groupAttributes;
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data, true);
    }

    /**
     * This function will return json decoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonDecodeData($data)
    {
        return $this->jsonHelper->jsonDecode($data, true);
    }

    /**
     * Check email
     *
     * @param string $email
     * @param int $website
     * @return bool
     */
    public function checkEmail($email, $website)
    {
        $profileCollection  = $this->uploadProfileFactory->create()
                ->getCollection()
                ->addFieldToFilter('email', ['eq'=>$email]);
        $customerCollection = $this->customerFactory->create()->getCollection()
            ->addAttributeToFilter('email', ['eq'=>$email]);
        
        if (!$profileCollection->getSize() && !$customerCollection->getSize()) {
            return true;
        }
            return false;
    }
    
    /**
     * Check Shop url
     *
     * @param string $shopUrl
     * @return bool
     */
    public function checkShopUrl($shopUrl)
    {
        $profileurlcount = $this->mpSellerFactory->create()->getCollection();
            $profileurlcount->addFieldToFilter(
                'shop_url',
                $shopUrl
            );
        $profileCollection  = $this->uploadProfileFactory->create()
                ->getCollection()
                ->addFieldToFilter('profileurl', ['eq'=>$shopUrl]);
        if (!$profileurlcount->getSize() && !$profileCollection->getSize()) {
            return true;
        }
        return false;
    }
    
    /**
     * Get VendorAttribute Code
     *
     * @return void
     */
    public function vendorAttributeCode()
    {
        $keys = [];
        if (!$this->vendorAttributeModule()) {
            return $keys;
        }
        $collection = $this->vendorAttributeCollection();
        foreach ($collection as $coll) {
            $keys[] = $coll->getAttributeCode();
        }
        return $keys;
    }

    /**
     * Check File and Media Attribute available
     *
     * @return boolean
     */
    public function getfileAndMedia()
    {
        if (!$this->vendorAttributeModule()) {
            return false;
        }
        $array = ['file','image'];
        $collection = $this->vendorAttributeCollection();
        $collection = $collection->addFieldToFilter("frontend_input", ["in" => $array]);
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }

    public function validateUploadedFiles($noValidate)
    {
        if (empty($noValidate)) {
            $validateZip = $this->validateZip();
            if ($validateZip['error']) {
                return $validateZip;
            }
        }
    }

    /**
     * Validate uploaded Images Zip File
     *
     * @return array
     */
    public function validateZip()
    {
        $allowedExtensions = $this->mergedAllowExtension();
        try {
            $imageUploader = $this->fileUploader->create(['fileId' => 'massupload_zip_file']);
            $imageUploader->setAllowedExtensions(['zip']);
            $validateData = $imageUploader->validateFile();
            $zipFilePath = $validateData['tmp_name'];
            $allowedImages = $allowedExtensions;
            $zip = zip_open($zipFilePath);
            if ($zip) {
                while ($zipEntry = zip_read($zip)) {
                    $fileName = zip_entry_name($zipEntry);
                    if (strpos($fileName, '.') !== false) {
                        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                        if (!in_array($ext, $allowedImages)) {
                            $msg = 'There are some files in zip which are not image.';
                            $result = ['error' => true, 'msg' => $msg];
                            return $result;
                        }
                    }
                }
                zip_close($zip);
            }
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->vendorlogger->info($e->getMessage());
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Images Zip File
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadZip($fileData, $params)
    {
        $profileLabel = $params['profile_label'];
        try {
            $zipModel = $this->zip;
            $basePath = $this->getBasePath($profileLabel);
            $imageUploadPath = $basePath.'zip/';
            $this->file->createDirectory($imageUploadPath);
            $imageUploader = $this->fileUploader->create(['fileId' => 'massupload_zip_file']);
            $validateData = $imageUploader->validateFile();
            $imageUploader->setAllowedExtensions(['zip']);
            $imageUploader->setAllowRenameFiles(true);
            $imageUploader->setFilesDispersion(false);
            $imageUploader->save($imageUploadPath);
            $fileName = $imageUploader->getUploadedFileName();
            $source = $imageUploadPath.$fileName;
            $mediaPath = $this->getMediaPath().'vendorfiles/image/';
            $destination =  $mediaPath.'tempfiles/';
            $zipModel->unzipImages($source, $destination);
            $this->arrangeFiles($destination);
            $this->flushFilesCache($destination);
            $this->copyFilesToDestinationFolder($profileLabel, $fileData, $mediaPath, 'image');
            $this->copyFilesToDestinationFolder($profileLabel, $fileData, $mediaPath, 'file');
            $result = ['error' => false];
            $this->file->deleteDirectory($destination);
        } catch (\Exception $e) {
            $this->flushData($profileLabel);
            $this->vendorlogger->info($e->getMessage());
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * get File and media Attribute Code array
     *
     * @param string $code
     * @return array
     */
    public function getFileAndMediaAttributeCode($code)
    {
        $attributeCode = [];
        $collection = $this->vendorAttributeCollection()->addFieldToSelect('attribute_code');
        $collection = $collection->addFieldToFilter("frontend_input", ["in" => $code]);
        foreach ($collection as $attributeData) {
            $attributeCode[] = $attributeData->getAttributeCode();
        }
        return $attributeCode;
    }

    /**
     * Upload Sample Files
     *
     * @param int $profileId
     * @param array $fileData
     * @param string $filePath
     * @param string $fileType
     *
     * @return array
     */
    public function copyFilesToDestinationFolder($profileLabel, $fileData, $filePath, $fileType)
    {
        $totalRows = $this->getCount($fileData);
        $shopIndex = '';
        $fileIndex = '';
        
        $fileTypeArray = $this->getFileAndMediaAttributeCode($fileType);
        
        foreach ($fileData[0] as $key => $value) {
            if ($key == 'profileurl') {
                $shopIndex = $key;
            }
            if (in_array($key, $fileTypeArray)) {
                $fileIndex = $key;
            }
        }
        $fileTempPath = $filePath.'tempfiles/';
        for ($i=0; $i < $totalRows; $i++) {
            if (!empty($fileData[$i][$shopIndex]) && !empty($fileData[$i][$fileIndex])) {
                $sku = $fileData[$i][$shopIndex];
                $destinationPath = $filePath.$sku;
                $isDestinationExist = 0;
                $file = $fileData[$i][$fileIndex];
                $sourcefilePath = $fileTempPath.$file;
                if ($this->fileDriver->isExists($sourcefilePath)) {
                    if ($fileType == 'file') {
                        $filePath = $this->getMediaPath().'vendorfiles/file/';
                        $destinationPath = $filePath.$sku;
                    }
                    if ($isDestinationExist == 0) {
                        $isDestinationExist = $this->createDirectoryAtDestination($destinationPath);
                    }
                    
                    $this->file->copy($sourcefilePath, $destinationPath.'/'.$file);
                }
            }
        }
    }

    /**
     * create directory at destination
     *
     * @param string $destinationPath
     * @return void
     */
    public function createDirectoryAtDestination($destinationPath)
    {
        $isDestinationExist = 0;
        if (!$this->fileDriver->isExists($destinationPath)) {
            $this->file->createDirectory($destinationPath);
            $isDestinationExist = 1;
        }
        return $isDestinationExist;
    }

    /**
     * Get Array Count
     *
     * @param array $array
     *
     * @return int
     */
    public function getCount($array)
    {
        return count($array);
    }

    /**
     * Get Media Path
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Base Path
     *
     * @param int $profileId
     *
     * @return string
     */
    public function getBasePath($profileId = 0)
    {
        $mediaPath = $this->getMediaPath();
        $basePath = $mediaPath.'marketplace/vendorupload/'.$profileId."/";
        return $basePath;
    }

    /**
     * Flush Unwanted Data
     *
     * @param int $profileId
     */
    public function flushData($profileId)
    {
        $path = $this->getBasePath($profileId);
        $this->flushFilesCache($path, true);
    }

    /**
     * Delte Extra Images and Folder
     *
     * @param string $path
     * @param bool $removeParent [optional]
     */
    public function flushFilesCache($path, $removeParent = false)
    {
        $entries = $this->fileDriver->readDirectory($path);
        foreach ($entries as $entry) {
            if ($this->fileDriver->isDirectory($entry)) {
                $this->removeDir($entry);
            }
        }
        if ($removeParent) {
            $this->removeDir($path);
        }
    }

    /**
     * Remove Folder and Its Content
     *
     * @param string $dir
     */
    public function removeDir($dir)
    {
        if ($this->fileDriver->isDirectory($dir)) {
            $entries = $this->fileDriver->readDirectory($dir);
            foreach ($entries as $entry) {
                if ($this->fileDriver->isFile($entry)) {
                    $this->fileDriver->deleteFile($entry);
                } else {
                    $this->removeDir($entry);
                }
            }
            $this->fileDriver->deleteDirectory($dir);
        }
    }

    /**
     * Rearrange Images of Product to upload
     *
     * @param string $path
     * @param string $originalPath [Optional]
     * @param array  $result [Optional]
     */
    public function arrangeFiles($path, $originalPath = '', $result = [])
    {
        if ($originalPath == '') {
            $originalPath = $path;
        }
        $entries = $this->fileDriver->readDirectory($path);
        foreach ($entries as $file) {
            if ($this->fileDriver->isDirectory($file)) {
                $result = $this->arrangeFiles($file, $originalPath, $result);
            } else {
                $tmp = explode("/", $file);
                $fileName = end($tmp);
                $sourcePath = $path.'/'.$fileName;
                $destinationPath = $originalPath.'/'.$fileName;
                if (!$this->fileDriver->isExists($destinationPath)) {
                    $result[$sourcePath] = $destinationPath;
                    $this->fileDriver->copy($sourcePath, $destinationPath);
                }
            }
        }
    }

    /**
     * Vendor attribute Allow Image Extensions
     *
     * @return array
     */
    public function getAllowedImageExtensions()
    {
        $allowedImageExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_image_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $allowedExtensions = explode(',', $allowedImageExtensions);
        array_walk($allowedExtensions, function (&$value) {
            $value = strtolower(trim($value));
        });
        
        return $allowedExtensions;
    }

    /**
     * Vendor attribute Allow File Extensions
     *
     * @return array
     */
    public function getAllowedFileExtensions()
    {
        $allowedFileExtensions = $this->scopeConfig->getValue(
            'marketplace/vendor_attribute/allowede_file_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $allowedExtensions = explode(',', $allowedFileExtensions);
        array_walk($allowedExtensions, function (&$value) {
            $value = strtolower(trim($value));
        });
        
        return $allowedExtensions;
    }

    /**
     * Vendor attribute Allow Image & file Extensions Merge
     *
     * @return array
     */
    public function mergedAllowExtension()
    {
        $imageExtensions = $this->getAllowedImageExtensions();
        $fileExtensions = $this->getAllowedFileExtensions();

        $allowedExtensions = array_merge($imageExtensions, $fileExtensions);
        return $allowedExtensions;
    }
}
