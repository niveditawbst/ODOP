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

use Webkul\MpVendorUpload\Api\Data\UploadProfileInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class UploadProfile extends AbstractModel implements UploadProfileInterface, IdentityInterface
{
    const CACHE_TAG = 'wk_vendor_upload';
    const TABLE_NAME = 'wk_vendor_upload';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_vendor_upload';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_vendor_upload';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpVendorUpload\Model\ResourceModel\UploadProfile::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getVendorGroup()
    {
        return $this->getData(self::VENDOR_GROUP);
    }

    public function setVendorGroup($vendorGroup)
    {
        return $this->setData(self::VENDOR_GROUP, $vendorGroup);
    }

    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    public function getProfileurl()
    {
        return $this->getData(self::PROFILEURL);
    }

    public function setProfileurl($profileUrl)
    {
        return $this->setData(self::PROFILEURL, $profileUrl);
    }

    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    public function setFirstname($firstName)
    {
        return $this->setData(self::FIRSTNAME, $firstName);
    }

    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    public function setLastname($lastName)
    {
        return $this->setData(self::LASTNAME, $lastName);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    public function getStatusMessage()
    {
        return $this->getData(self::STATUS_MESSAGE);
    }

    public function setStatusMessage($statusMessage)
    {
        return $this->setData(self::STATUS_MESSAGE, $statusMessage);
    }
}
