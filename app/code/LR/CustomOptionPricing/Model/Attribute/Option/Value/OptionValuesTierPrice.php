<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace LR\CustomOptionPricing\Model\Attribute\Option\Value;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use LR\CustomOptionPricing\Model\OptionsTierPrice as OptionsTierPriceModel;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Helper\System as SystemHelper;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class OptionValuesTierPrice extends AbstractAttribute
{
    const FIELD_MAGE_ONE_OPTIONS_IMPORT = '_custom_option_row_tier_data';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var TierPriceModel
     */
    protected $optionsTierPriceModel;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     * @param BaseHelper $baseHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param TierPriceModel $tierPriceModel
     * @param SystemHelper $systemHelper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper,
        BaseHelper $baseHelper,
        DataObjectFactory $dataObjectFactory,
        OptionsTierPriceModel $optionsTierPriceModel,
        SystemHelper $systemHelper
    ) {
        $this->helper         = $helper;
        $this->optionsTierPriceModel = $optionsTierPriceModel;
        $this->systemHelper   = $systemHelper;
        parent::__construct($resource, $baseHelper, $dataObjectFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return optionsTierPriceModel::KEY_TIER_PRICE;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => optionsTierPriceModel::TABLE_NAME,
            'group'   => optionsTierPriceModel::OPTIONTEMPLATES_TABLE_NAME
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }
        return $map[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, array $options)
    {
        

        $this->entity = $entity;

        $tierPrices = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[$this->getName()])) {
                    continue;
                }
                $tierPrices[$value[optionsTierPriceModel::COLUMN_OPTION_TYPE_ID]] = $value[$this->getName()];
            }
        }

        return $this->collectTierPrices($tierPrices);
    }

    /**
     * Collect tier prices
     *
     * @param array $items
     * @return array
     */
    protected function collectTierPrices($items)
    {
        $customerGroupIds = $this->systemHelper->getCustomerGroupIds(true);
        $data             = [];

        foreach ($items as $itemKey => $itemValue) {
            $data['delete'][] = [
                optionsTierPriceModel::COLUMN_OPTION_TYPE_ID => $itemKey,
            ];
            $decodedJsonData  = json_decode($itemValue, true);
            if (empty($decodedJsonData) || !is_array($decodedJsonData)) {
                continue;
            }
            foreach ($decodedJsonData as $dataItem) {
                if (!in_array(
                    $dataItem[optionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID],
                    array_values($customerGroupIds)
                )) {
                    continue;
                }

                $price     = $dataItem[optionsTierPriceModel::COLUMN_PRICE];
                $squareArea = $dataItem[optionsTierPriceModel::COLUMN_SQUARE_AREA];
                
                $customerGroup = (int)$dataItem[optionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID];
                /*if (!$customerGroup) {
                    $customerGroup = 32000;
                }*/

                $data['save'][] = [
                    optionsTierPriceModel::COLUMN_OPTION_TYPE_ID    => $itemKey,
                    optionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID => $customerGroup,
                    optionsTierPriceModel::COLUMN_PRICE             => $price,
                    optionsTierPriceModel::COLUMN_SQUARE_AREA         => $squareArea,
                ];
            }
        }
        return $data;
    }

    /**
     * Delete old option value tier prices
     *
     * @param $data
     * @return void
     */
    public function deleteOldData(array $data)
    {
        $optionValueIds = [];
        foreach ($data as $dataItem) {
            $optionValueIds[] = $dataItem[optionsTierPriceModel::COLUMN_OPTION_TYPE_ID];
        }
        if (!$optionValueIds) {
            return;
        }
        $tableName  = $this->resource->getTableName($this->getTableName());
        $conditions = optionsTierPriceModel::COLUMN_OPTION_TYPE_ID .
            " IN (" . implode(",", $optionValueIds) . ")";
        $this->resource->getConnection()->delete($tableName, $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        $tierPrices = $this->optionsTierPriceModel->getSuitableTierPrices($object, true);
        if (!$tierPrices) {
            return [];
        } else {
            return [$this->getName() => json_encode($tierPrices)];
        }
    }

    /**
     * Process attribute in case of product/group duplication
     *
     * @param string $newId
     * @param string $oldId
     * @param string $entityType
     * @return void
     */
    public function processDuplicate($newId, $oldId, $entityType = 'product')
    {
        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName($this->getTableName($entityType));

        $select = $connection->select()->from(
            $table,
            [
                new \Zend_Db_Expr($newId),
                OptionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                OptionsTierPriceModel::COLUMN_PRICE,
                OptionsTierPriceModel::COLUMN_SQUARE_AREA
            ]
        )->where(
            OptionsTierPriceModel::COLUMN_OPTION_TYPE_ID . ' = ?',
            $oldId
        );

        $insertSelect = $connection->insertFromSelect(
            $select,
            $table,
            [
                OptionsTierPriceModel::COLUMN_OPTION_TYPE_ID,
                OptionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID,
                OptionsTierPriceModel::COLUMN_PRICE,
                OptionsTierPriceModel::COLUMN_SQUARE_AREA
            ]
        );
        $connection->query($insertSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function validateTemplateMageOne($data)
    {
        if (!isset($data['tiers']) || !is_array($data['tiers'])) {
            return true;
        }

        foreach ($data['tiers'] as $tierPriceItem) {
            if (!isset($tierPriceItem['customer_group_id'])) {
                throw new LocalizedException(
                    __("Tier price's field '%1' not found", 'customer_group_id')
                );
            }
            if (!isset($tierPriceItem['price'])) {
                throw new LocalizedException(
                    __("Tier price's field '%1' not found", 'price')
                );
            }
            if (!isset($tierPriceItem['price_type'])) {
                throw new LocalizedException(
                    __("Tier price's field '%1' not found", 'price_type')
                );
            }
            if (!isset($tierPriceItem['qty'])) {
                throw new LocalizedException(
                    __("Tier price's field '%1' not found", 'qty')
                );
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        $tierPrices = [];
        if (!isset($data['tiers']) || !is_array($data['tiers'])) {
            return '';
        }

        foreach ($data['tiers'] as $tierPriceItem) {
            if ($tierPriceItem['price_type'] == 'percent') {
                $price     = 100 - $tierPriceItem['price'];
                $priceType = Helper::PRICE_TYPE_PERCENTAGE_DISCOUNT;
            } else {
                $price     = $tierPriceItem['price'];
                $priceType = $tierPriceItem['price_type'];
            }
            $tierPrices[] = [
                optionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID => $tierPriceItem['group_id'],
                optionsTierPriceModel::COLUMN_PRICE             => $price,
                optionsTierPriceModel::COLUMN_SQUARE_AREA       => $tierPriceItem['square_area']
            ];
        }

        return json_encode($tierPrices);
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageTwo($data)
    {
        return isset($data[$this->getName()]) ? json_encode($data[$this->getName()]) : null;
    }

    /**
     * Collect system data (customer group ids, store ids) from Magento 1 product csv
     *
     * @param array $systemData
     * @param array $productData
     * @param array $optionData
     * @param array $valueData
     */
    public function collectOptionsSystemDataMageOne(&$systemData, $productData, $optionData, $valueData = [])
    {
        if (!isset($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            || $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] === ''
        ) {
            return;
        }

        if (is_array($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])) {
            if (isset($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT][0])) {
                $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] = $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT][0];
            } else {
                return;
            }
        }

        $tierPrices = explode('|', $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT]);
        foreach ($tierPrices as $tierPrice) {
            list($customerGroupId, $qty, $price, $priceType) = explode(':', $tierPrice);
            if ($customerGroupId == 32000) {
                continue;
            }
            $systemData['customer_group'][$customerGroupId] = $customerGroupId;
        }
    }

    /**
     * Prepare data from Magento 1 product csv for future import
     *
     * @param array $systemData
     * @param array $productData
     * @param array $optionData
     * @param array $preparedOptionData
     * @param array $valueData
     * @param array $preparedValueData
     * @return void
     */
    public function prepareOptionsMageOne(
        $systemData,
        $productData,
        $optionData,
        &$preparedOptionData,
        $valueData = [],
        &$preparedValueData = []
    ) {
        if (!isset($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])
            || $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] === ''
        ) {
            return;
        }

        if (is_array($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT])) {
            if (isset($valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT][0])) {
                $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT] = $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT][0];
            } else {
                return;
            }
        }

        $data       = [];
        $tierPrices = explode('|', $valueData[static::FIELD_MAGE_ONE_OPTIONS_IMPORT]);
        foreach ($tierPrices as $tierPrice) {
            list($squareArea, $groupId, $price) = explode(':', $tierPrice);
            if (!$this->hasCustomerGroupEquivalent($systemData, $groupId)) {
                continue;
            }
            $data[] = [
                optionsTierPriceModel::COLUMN_CUSTOMER_GROUP_ID => $systemData['map']['customer_group'][$groupId],
                optionsTierPriceModel::COLUMN_PRICE             => $price,
                optionsTierPriceModel::COLUMN_SQUARE_AREA        => $squareArea

            ];
        }
        $preparedValueData[static::getName()] = $this->baseHelper->jsonEncode($data);
    }

    /**
     * Collect data for magento2 product export
     *
     * @param array $row
     * @param array $data
     * @return void
     */
    public function collectExportDataMageTwo(&$row, $data)
    {
        $prefix        = 'custom_option_row_';
        $attributeData = null;
        if (!empty($data[$this->getName()])) {
            $attributeData = $this->baseHelper->jsonDecode($data[$this->getName()]);
        }
        if (empty($attributeData) || !is_array($attributeData)) {
            $row[$prefix . $this->getName()] = null;
            return;
        }
        $result = [];
        foreach ($attributeData as $datum) {
            $parts = [];
            foreach ($datum as $datumKey => $datumValue) {
                $datumValue = $this->encodeSymbols($datumValue);
                $parts[]    = $datumKey . '=' . $datumValue . '';
            }
            $result[] = implode(',', $parts);
        }
        $row[$prefix . $this->getName()] = $result ? implode('|', $result) : null;
    }

    /**
     * Collect data for magento2 product import
     *
     * @param array $data
     * @return array|null
     */
    public function collectImportDataMageTwo($data)
    {
        if (!$this->hasOwnTable()) {
            return null;
        }

        if (!isset($data['custom_option_row_' . $this->getName()])) {
            return null;
        }

        $this->entity = $this->dataObjectFactory->create();
        $this->entity->setType('product');

        $tierPrices   = [];
        $preparedData = [];
        $iterator     = 0;

        $attributeData = $data['custom_option_row_' . $this->getName()];
        if (empty($attributeData)) {
            return $this->collectTierPrices($tierPrices);
        }

        $step1 = explode('|', $attributeData);
        foreach ($step1 as $step1Item) {
            $step2 = explode(',', $step1Item);
            foreach ($step2 as $step2Item) {
                $step3Item                              = explode('=', $step2Item);
                $step3Item[1]                           = $this->decodeSymbols($step3Item[1]);
                $preparedData[$iterator][$step3Item[0]] = $step3Item[1];
            }
            $iterator++;
        }
        $tierPrices[$data['custom_option_row_id']] = $this->baseHelper->jsonEncode($preparedData);
        return $this->collectTierPrices($tierPrices);
    }
}
