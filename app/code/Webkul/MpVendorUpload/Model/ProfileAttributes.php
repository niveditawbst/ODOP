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

use Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ProfileAttributes extends AbstractModel implements ProfileAttributesInterface, IdentityInterface
{
    const CACHE_TAG = 'wk_vendor_profile_attributes';
    const TABLE_NAME = 'wk_vendor_profile_attributes';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_vendor_profile_attributes';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_vendor_profile_attributes';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpVendorUpload\Model\ResourceModel\ProfileAttributes::class);
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

    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
