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

namespace Webkul\MpVendorUpload\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class MoveMediaFiles implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesSystem;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Reader $reader
     * @param Filesystem $filesSystem
     * @param File $fileDriver
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Reader $reader,
        Filesystem $filesSystem,
        File $fileDriver
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->reader = $reader;
        $this->filesystem = $filesSystem;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moveDirToMediaDir();
    }

    /**
     * Copy Sample CSV,XML and XSL files to Media
     */
    private function moveDirToMediaDir()
    {
        try {
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $smpleFilePath = $this->filesystem->getDirectoryRead($type)
                                        ->getAbsolutePath().'marketplace/vendorupload/';
            $files = [
                'sample.csv',
                'sample.xml',
                'sample.xls',
                'sample_vendorattribute.csv',
                'sample_vendorattribute.xls',
                'sample_vendorattribute.xml'
            ];
            if ($this->fileDriver->isExists($smpleFilePath)) {
                $this->fileDriver->deleteDirectory($smpleFilePath);
            }
            if (!$this->fileDriver->isExists($smpleFilePath)) {
                $this->fileDriver->createDirectory($smpleFilePath, 0777);
            }
            foreach ($files as $file) {
                $filePath = $smpleFilePath.$file;
                if (!$this->fileDriver->isExists($filePath)) {
                    $path = '/pub/media/marketplace/vendorupload/'.$file;
                    $mediaFile = $this->reader->getModuleDir('', 'Webkul_MpVendorUpload').$path;
                    if ($this->fileDriver->isExists($mediaFile)) {
                        $this->fileDriver->copy($mediaFile, $filePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
