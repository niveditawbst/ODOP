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
 * Interface for Management of UploadProfile.
 * @api
 */
interface UploadProfileRepositoryInterface
{
    /**
     * Create or update an profile
     *
     * @param \Webkul\MpVendorUpload\Api\Data\UploadProfileInterface $uploadProfile
     * @return \Webkul\MpVendorUpload\Api\Data\UploadProfileInterface
     */
    public function save(\Webkul\MpVendorUpload\Api\Data\UploadProfileInterface $uploadProfile);

    /**
     * Get Upload Profile by uploadProfileId
     *
     * @param int $uploadProfileId
     * @return \Webkul\MpVendorUpload\Api\Data\UploadProfileInterface
     */
    public function getById($uploadProfileId);

    /**
     * Delete Upload Profile
     *
     * @param \Webkul\MpVendorUpload\Api\Data\UploadProfileInterface $uploadProfile
     * @return bool true on success
     */
    public function delete(\Webkul\MpVendorUpload\Api\Data\UploadProfileInterface $uploadProfile);

    /**
     * Delete Upload Profile by ID.
     *
     * @param int $uploadProfileId
     * @return bool true on success
     */
    public function deleteById($uploadProfileId);
}
