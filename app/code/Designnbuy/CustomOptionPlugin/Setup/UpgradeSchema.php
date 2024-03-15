<?php
/**
 * Copyright © 2020 Design'N'Buy (inquiry@designnbuy.com). All rights reserved.
 * 
 */
namespace Designnbuy\CustomOptionPlugin\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const DESIGNTOOL_TITLE = 'designtool_title';
    const DESIGNTOOL_TYPE = 'designtool_type';
    const CATALOG_PRODUCT_OPTION_TABLE = 'catalog_product_option';
    const CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE = 'catalog_product_option_type_value';

    const MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION = 'mageworx_optiontemplates_group_option';
    const MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TYPE_VALUE = 'mageworx_optiontemplates_group_option_type_value';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        
        //add designtool_title for catalog_product_option table
        $installer->getConnection()->addColumn(
            $setup->getTable(static::CATALOG_PRODUCT_OPTION_TYPE_VALUE_TABLE),
            static::DESIGNTOOL_TITLE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'DesignTool Title (added by Designnbuy)',
                'after' => 'option_id'
            ]
        );
        

        
        //add designtool_type for catalog_product_option table
        $installer->getConnection()->addColumn(
            $setup->getTable(static::CATALOG_PRODUCT_OPTION_TABLE),
            static::DESIGNTOOL_TYPE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'DesignTool Type (added by Designnbuy)',
                //'after' => 'one_time'
            ]
        );
        

        

        //add designtool_type for mageworx_optiontemplates_group_option table
        $installer->getConnection()->addColumn(
            $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION),
            static::DESIGNTOOL_TYPE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'nullable' => true,
                'comment' => 'DesignTool Type (added by Designnbuy)',
                //'after' => 'one_time'
            ]
        );

        //add designtool_title for mageworx_optiontemplates_group_option_type_value table
        $installer->getConnection()->addColumn(
            $setup->getTable(static::MAGEWORX_OPTIONTEMPLATES_GROUP_OPTION_TYPE_VALUE),
            static::DESIGNTOOL_TITLE,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'DesignTool Title (added by Designnbuy)',
                'after' => 'option_id'
            ]
        );
        

        $installer->endSetup();
    }
}
