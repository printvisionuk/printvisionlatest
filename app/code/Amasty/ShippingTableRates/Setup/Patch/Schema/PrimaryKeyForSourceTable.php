<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Setup\Patch\Schema;

use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * compatibility with m2.4.2
 * Declarative Schema can't add autoincrement column if existing table hasn't primary key
 */
class PrimaryKeyForSourceTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $setup;

    public function __construct(
        SchemaSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    public function apply(): void
    {
        $this->setup->getConnection()->addColumn(
            $this->setup->getTable(Rate::SOURCES_TABLE),
            'id',
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Id',
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ]
        );
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
