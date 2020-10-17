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
namespace Webkul\MpVendorUpload\Model;

use Magento\Framework\Archive;

class Zip extends Archive
{
    /**
     * Unzip Images.
     *
     * @param string $source
     * @param string $destination
     */
    public function unzipImages($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $zip->extractTo($destination);
        $zip->close();
    }
}
