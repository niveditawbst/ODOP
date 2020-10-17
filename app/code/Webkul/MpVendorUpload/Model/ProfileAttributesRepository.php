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
use Webkul\MpVendorUpload\Api\ProfileAttributesRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpVendorUpload\Model\ResourceModel\ProfileAttributes as ProfileAttributesResource;
use Webkul\MpVendorUpload\Model\ResourceModel\ProfileAttributes\CollectionFactory as ProfileAttributesCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProfileAttributes for profile
 */
class ProfileAttributesRepository implements ProfileAttributesRepositoryInterface
{
    /**
     * @var ProfileAttributesResource
     */
    protected $resource;

    /**
     * @var ProfileAttributesFactory
     */
    protected $profileAttributesFactory;

    /**
     * @var ProfileAttributesCollection
     */
    protected $profileAttributesCollectionFactory;

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
     * @param ProfileAttributesResource   $resource
     * @param ProfileAttributesFactory    $profileAttributesFactory
     * @param ProfileAttributesCollection $profileAttributesCollectionFactory
     * @param DataObjectHelper            $dataObjectHelper
     * @param DataObjectProcessor         $dataObjectProcessor
     * @param StoreManagerInterface       $storeManager
     */
    public function __construct(
        ProfileAttributesResource $resource,
        ProfileAttributesFactory $profileAttributesFactory,
        ProfileAttributesCollection $profileAttributesCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->profileAttributesFactory = $profileAttributesFactory;
        $this->profileAttributesCollectionFactory = $profileAttributesCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Profile Attributes Complete data
     *
     * @param  Data\ProfileAttributesInterface $profileAttributes
     * @return ProfileAttributes
     * @throws CouldNotSaveException
     */
    public function save(Data\ProfileAttributesInterface $profileAttributes)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $order->setStoreId($storeId);
        try {
            $this->resource->save($profileAttributes);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $profileAttributes;
    }

    /**
     * Load Profile Attributes Complete data by given Block Identity
     *
     * @param string $id
     * @return ProfileAttributes
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $profileAttributes = $this->profileAttributesFactory->create();
        $this->resource->load($profileAttributes, $id);
        if (!$profileAttributes->getId()) {
            throw new NoSuchEntityException(__('Profile Attributes with id "%1" does not exist.', $id));
        }
        return $profileAttributes;
    }

    /**
     * Delete Profile Attributes
     *
     * @param  Data\ProfileAttributesInterface $profileAttributes
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ProfileAttributesInterface $profileAttributes)
    {
        try {
            $this->resource->delete($profileAttributes);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Profile Attributes by given Block Identity
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
