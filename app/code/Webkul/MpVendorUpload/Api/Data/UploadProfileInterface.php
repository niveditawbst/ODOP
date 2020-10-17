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

namespace Webkul\MpVendorUpload\Api\Data;

interface UploadProfileInterface
{
    const ID              = 'id';
    const VENDOR_GROUP    = 'vendor_group';
    const LABEL           = 'label';
    const PROFILEURL      = 'profileurl';
    const EMAIL           = 'email';
    const FIRSTNAME       = 'firstname';
    const GROUP_ID        = 'group_id';
    const LASTNAME        = 'lastname';
    const STORE_ID        = 'store_id';
    const WEBSITE_ID      = 'website_id';
    const STATUS_MESSAGE  = 'status_message';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Vendor Group
     *
     * @return int|null
     */
    public function getVendorGroup();

    /**
     * Get Label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Get Profile URL
     *
     * @return string|null
     */
    public function getProfileurl();

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Get First Name
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Get Group ID
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get Last Name
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Get Website ID
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Get Status Message
     *
     * @return string|null
     */
    public function getStatusMessage();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Vendor Group
     *
     * @return int|null
     */
    public function setVendorGroup($vendorGroup);

    /**
     * Set Label
     *
     * @return string|null
     */
    public function setLabel($label);

    /**
     * Set Profile URL
     *
     * @return string|null
     */
    public function setProfileurl($profileUrl);

    /**
     * Set Email
     *
     * @return string|null
     */
    public function setEmail($email);

    /**
     * Set First Name
     *
     * @return string|null
     */
    public function setFirstname($firstName);

    /**
     * Set Group ID
     *
     * @return int|null
     */
    public function setGroupId($groupId);

    /**
     * Set Last Name
     *
     * @return string|null
     */
    public function setLastname($lastName);

    /**
     * Set Store ID
     *
     * @return int|null
     */
    public function setStoreId($storeId);

    /**
     * Set Website ID
     *
     * @return int|null
     */
    public function setWebsiteId($websiteId);

    /**
     * Set Status Message
     *
     * @return string|null
     */
    public function setStatusMessage($statusMessage);
}
