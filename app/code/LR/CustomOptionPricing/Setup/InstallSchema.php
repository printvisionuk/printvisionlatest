<?php
namespace LR\CustomOptionPricing\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
   public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
   {
       $setup->startSetup();

       $setup->getConnection()->addColumn(
           $setup->getTable('catalog_product_option'),
           'is_custom_pricing',
           [
               'type'     => Table::TYPE_BOOLEAN,
               'unsigned' => true,
               'nullable' => false,
               'default'  => '0',
               'comment'  => 'Custom Pricing',
           ]
       );
       $tableName = 'lr_custom_pricing_product_option_tierprice';
       $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Option Value TierPrice ID'
                )
                ->addColumn(
                    'option_type_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => false,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => false
                    ],
                    'Option Value Primary Key'
                )
                ->addColumn(
                    'square_area',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Title'
                )
                ->addColumn(
                    'group_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Description'
                )
                ->addColumn(
                    'price',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Description'
                )
                ->addIndex(
                    $setup->getIdxName('lr_custom_pricing_product_option_tierprice', ['option_type_id']),
                    ['option_type_id']
                )
                //Set comment for magetop_blog table
                ->setComment('LR Custom Option Value Tier Price')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);

       $setup->endSetup();
   }
}