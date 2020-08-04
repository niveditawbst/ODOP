<?php

namespace Techelogy\Checkoutfield\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote_address'),
            'alternate_mobile_number',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Alternate Mobile Number',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_address'),
            'alternate_mobile_number',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Alternate Mobile Number',
            ]
        );

        $setup->endSetup();
    }
}
