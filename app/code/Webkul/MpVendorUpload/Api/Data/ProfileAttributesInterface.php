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

interface ProfileAttributesInterface
{
    const ID          = 'id';
    const PROFILE_ID  = 'profile_id';
    const CODE        = 'code';
    const VALUE       = 'value';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Profile ID
     *
     * @return int|null
     */
    public function getProfileId();

    /**
     * Get Code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get Value
     *
     * @return string|null
     */
    public function getValue();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Profile ID
     *
     * @return int|null
     */
    public function setProfileId($profileId);

    /**
     * Set Code
     *
     * @return string|null
     */
    public function setCode($code);

    /**
     * Set Value
     *
     * @return string|null
     */
    public function setValue($value);
}
