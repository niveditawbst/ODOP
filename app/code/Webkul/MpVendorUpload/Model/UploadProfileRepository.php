<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpVendorUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpVendorUpload\Model;

use Webkul\MpVendorUpload\Api\Data;
use Webkul\MpVendorUpload\Api\UploadProfileRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpVendorUpload\Model\ResourceModel\UploadProfile as UploadProfileResource;
use Webkul\MpVendorUpload\Model\ResourceModel\UploadProfile\CollectionFactory as UploadProfileCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UploadProfile for profile
 */
class UploadProfileRepository implements UploadProfileRepositoryInterface
{
    /**
     * @var UploadProfileResource
     */
    protected $resource;

    /**
     * @var UploadProfileFactory
     */
    protected $uploadProfileFactory;

    /**
     * @var UploadProfileCollection
     */
    protected $uploadProfileCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param UploadProfileResource   $resource
     * @param UploadProfileFactory    $uploadProfileFactory
     * @param UploadProfileCollection $uploadProfileCollectionFactory
     * @param DataObjectHelper        $dataObjectHelper
     * @param DataObjectProcessor     $dataObjectProcessor
     * @param StoreManagerInterface   $storeManager
     */
    public function __construct(
        UploadProfileResource $resource,
        UploadProfileFactory $uploadProfileFactory,
        UploadProfileCollection $uploadProfileCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->uploadProfileFactory = $uploadProfileFactory;
        $this->uploadProfileCollectionFactory = $uploadProfileCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Upload Profile Complete data
     *
     * @param  Data\UploadProfileInterface $uploadProfile
     * @return UploadProfile
     * @throws CouldNotSaveException
     */
    public function save(Data\UploadProfileInterface $uploadProfile)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $order->setStoreId($storeId);
        try {
            $this->resource->save($uploadProfile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $uploadProfile;
    }

    /**
     * Load Upload Profile Complete data by given Block Identity
     *
     * @param string $id
     * @return UploadProfile
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $uploadProfile = $this->uploadProfileFactory->create();
        $this->resource->load($uploadProfile, $id);
        if (!$uploadProfile->getEntityId()) {
            throw new NoSuchEntityException(__('Upload Profile with id "%1" does not exist.', $id));
        }
        return $uploadProfile;
    }

    /**
     * Delete Upload Profile
     *
     * @param  Data\UploadProfileInterface $uploadProfile
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\UploadProfileInterface $uploadProfile)
    {
        try {
            $this->resource->delete($uploadProfile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Upload Profile by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
