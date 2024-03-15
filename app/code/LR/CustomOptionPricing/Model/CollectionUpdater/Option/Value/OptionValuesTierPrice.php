<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace LR\CustomOptionPricing\Model\CollectionUpdater\Option\Value;

use MageWorx\OptionBase\Model\Product\Option\AbstractUpdater;
use LR\CustomOptionPricing\Model\OptionsTierPrice as OptionsTierPriceModel;

class OptionValuesTierPrice extends AbstractUpdater
{
    /**
     * {@inheritdoc}
     */
    public function getFromConditions(array $conditions)
    {
        $alias = $this->getTableAlias();
        $table = $this->getTable($conditions);
        return [$alias => $table];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($entityType)
    {
        /*if ($entityType == 'group') {
            return $this->resource->getTableName(OptionsTierPriceModel::OPTIONTEMPLATES_TABLE_NAME);
        }*/
        return $this->resource->getTableName(OptionsTierPriceModel::TABLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getOnConditionsAsString()
    {
        return 'main_table.' . OptionsTierPriceModel::COLUMN_OPTION_TYPE_ID . ' = '
            . $this->getTableAlias() . '.' . OptionsTierPriceModel::COLUMN_OPTION_TYPE_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [OptionsTierPriceModel::KEY_TIER_PRICE => $this->getTableAlias() . '.' . OptionsTierPriceModel::KEY_TIER_PRICE];
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return $this->resource->getConnection()->getTableName('lr_custom_pricing_product_option_tierprice');
    }

    /**
     * Get table for from conditions
     *
     * @param array $conditions
     * @return \Zend_Db_Expr
     */
    private function getTable($conditions)
    {
        $entityType = $conditions['entity_type'];
        $tableName  = $this->getTableName($entityType);

        $selectExpr = "SELECT " . OptionsTierPriceModel::COLUMN_OPTION_TYPE_ID . " as "
            . OptionsTierPriceModel::FIELD_OPTION_TYPE_ID_ALIAS . ","
            . " CONCAT('[',"
            . " GROUP_CONCAT(CONCAT("
            . "'{\"price\"',':\"',IFNULL(price,''),'\",',"
            . "'\"group_id\"',':\"',group_id,'\",',"
            . "'\"square_area\"',':\"',square_area,'\"}'"
            . ")),"
            . "']')"
            . " AS values_tier_price FROM " . $tableName;
        
        if ($conditions && (!empty($conditions['option_id']) || !empty($conditions['value_id']))) {
            $optionTypeIds = $this->helper->findOptionTypeIdByConditions($conditions);
            if (is_array($optionTypeIds) && count($optionTypeIds) > 0) {
               
                $selectExpr .= " WHERE option_type_id IN(" . implode(',', $optionTypeIds) . ")";
            }
        }
        $selectExpr .= " GROUP BY option_type_id";

        return new \Zend_Db_Expr('(' . $selectExpr . ')');
    }
}
