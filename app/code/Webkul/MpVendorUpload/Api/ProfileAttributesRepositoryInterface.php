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

namespace Webkul\MpVendorUpload\Api;

/**
 * Interface for Management of ProfileAttributes
 */
interface ProfileAttributesRepositoryInterface
{
    /**
     * Create or update a profile attribute
     *
     * @param \Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface $profileAttribute
     * @return \Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface
     */
    public function save(\Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface $profileAttribute);

    /**
     * Get Upload Profile by profileAttributeId
     *
     * @param int $profileAttributeId
     * @return \Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface
     */
    public function getById($profileAttributeId);

    /**
     * Delete Profile Attribute
     *
     * @param \Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface $profileAttribute
     * @return bool true on success
     */
    public function delete(\Webkul\MpVendorUpload\Api\Data\ProfileAttributesInterface $profileAttribute);

    /**
     * Delete Profile Attribute by ID.
     *
     * @param int $profileAttributeId
     * @return bool true on success
     */
    public function deleteById($profileAttributeId);
}
