<?php
 namespace Axtrics\Aframark\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('aframark_settings')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('aframark_settings')
            )
                ->addColumn(
                    'app_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Request Id'
                )
                ->addColumn(
                    'app_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'App Key'
                )
                ->addColumn(
                    'secret_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Secret Key'
                )
               ->addColumn(
                   'merchant_code',
                   \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                   255,
                   [],
                   'Merchant Code'
               )
                ->addColumn(
                    'store_token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Store Token'
                )
                ->addColumn(
                    'upc_attribute_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'UPC Attribute Code'
                )
                ->addColumn(
                    'ean_attribute_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Ean Attribute Code'
                )
                ->addColumn(
                    'mpn_attribute_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Mpn Attribute Code'
                )
                ->addColumn(
                    'isbn_attribute_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Isbn Attribute Code'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [],
                    'Enabled'
                )
                ->addColumn(
                    'version',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Version'
                )->addColumn(
                    'store_connected',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [],
                    'Store Connected'
                )
                ->addColumn(
                    'last_connection_response_on',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    11,
                    [],
                    'Last Connection Time'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->setComment('Aframark Record');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('aframark_settings'),
                $setup->getIdxName(
                    $installer->getTable('aframark_settings'),
                    ['app_key','secret_key','merchant_code','store_token','upc_attribute_code','mpn_attribute_code','isbn_attribute_code','ean_attribute_code','version'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['app_key','secret_key','merchant_code','store_token','upc_attribute_code','ean_attribute_code','mpn_attribute_code','isbn_attribute_code','version'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
