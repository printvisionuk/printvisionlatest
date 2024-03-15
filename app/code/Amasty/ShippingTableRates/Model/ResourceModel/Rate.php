<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Helper\Data;
use Amasty\ShippingTableRates\Model\ConfigProvider;
use Amasty\ShippingTableRates\Model\Import\Rate\Import;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate\SaveSources;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Rate extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_table_rate';
    public const SOURCES_TABLE = 'amasty_table_rate_sources';
    public const FIND_PATTERN = '~^[\p{L}\p{Z}-]+$~u';

    public const SOURCES_REQUEST_KEY = 'amstrates_sources';

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var SaveSources
     */
    private $saveSources;

    /**
     * @var int
     */
    public $importFlag = 0;

    public function __construct(
        TableMaintainer $tableMaintainer,
        ConfigProvider $configProvider,
        Data $helper,
        Context $context,
        Generator $queryGenerator,
        SaveSources $saveSources,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->tableMaintainer = $tableMaintainer;
        $this->configProvider = $configProvider;
        $this->helper = $helper;
        $this->queryGenerator = $queryGenerator;
        $this->saveSources = $saveSources;
    }

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, ShippingTableRateInterface::ID);
    }

    /**
     * Save sources after saving main model
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return Rate
     */
    public function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $sourceCodes = explode(',', (string)$object->getData('source_codes'));

        if (!empty($sourceCodes)) {
            $this->saveSources->execute((int)$object->getId(), $sourceCodes);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param int $methodId
     */
    public function deleteBy($methodId)
    {
        $this->getConnection()->delete($this->getMainTable(), 'method_id=' . (int)$methodId);
    }

    /**
     * @param array $data
     */
    public function insertBunch(array $data): void
    {
        foreach ($data as $rate) {
            $sources = [];
            if (isset($rate['source'])) {
                $sources = explode(',', $rate['source']);
            }
            unset($rate['source']);
            $this->getConnection()->insert($this->tableMaintainer->getTable(self::MAIN_TABLE), $rate);

            if (empty($sources)) {
                continue;
            }

            $rateId = $this->getConnection()->lastInsertId();
            $insertSources = [];
            foreach ($sources as $source) {
                $insertSources[] = [
                    'rate_id' => $rateId,
                    'source' => $source
                ];
            }
            $this->getConnection()->insertMultiple(
                $this->tableMaintainer->getTable(self::SOURCES_TABLE),
                $insertSources
            );
        }
    }

    /**
     * @return string
     */
    public function getMainTable(): string
    {
        if ($this->importFlag == Import::STATE_ACTIVE) {
            return $this->tableMaintainer->getReplicaTable(self::MAIN_TABLE);
        }

        return parent::getMainTable();
    }

    /**
     * @param array $methodIds
     * @param array $shippingTypes
     * @return array where key = method_id, value = shipping types array
     */
    public function getUniqueRateTypes(array $methodIds, array $shippingTypes): array
    {
        $ratesTypes = [];
        $select = $this->getConnection()->select()
            ->from(
                $this->getMainTable(),
                [
                    ShippingTableRateInterface::METHOD_ID,
                    ShippingTableRateInterface::SHIPPING_TYPE
                ]
            )->where(
                ShippingTableRateInterface::METHOD_ID . ' IN(?)',
                $methodIds
            )->where(
                ShippingTableRateInterface::SHIPPING_TYPE . ' IN(?)',
                $shippingTypes
            )->order(
                ShippingTableRateInterface::SHIPPING_TYPE . ' ' . Select::SQL_DESC
            )->group(
                [
                    ShippingTableRateInterface::SHIPPING_TYPE,
                    ShippingTableRateInterface::METHOD_ID
                ]
            );

        foreach ((array)$this->getConnection()->fetchAll($select) as $item) {
            $ratesTypes[(int)$item[ShippingTableRateInterface::METHOD_ID]][]
                = (int)$item[ShippingTableRateInterface::SHIPPING_TYPE];
        }

        return $ratesTypes;
    }

    /**
     * @param RateRequest $request
     * @param int $methodId
     * @param array $totals
     * @param int $shippingType
     * @param bool $allowFreePromo
     * @return array
     */
    public function getMethodRates(
        RateRequest $request,
        int $methodId,
        array $totals,
        int $shippingType,
        bool $allowFreePromo
    ): array {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(). ' AS main_table')
            ->where(ShippingTableRateInterface::METHOD_ID . ' = ?', $methodId);

        $this->addSourceFilters($select, $request);
        $this->addAddressFilters($select, $request);
        $this->addTotalsFilters($select, $totals, $shippingType, $request, $allowFreePromo);

        return (array)$this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param Select $select
     * @param RateRequest $request
     * @return $this
     */
    public function addSourceFilters(Select $select, RateRequest $request): self
    {
        $sources = $request->getData('amstrates_sources');
        if ($sources) {
            $select->joinLeft(
                ['rs' => $this->getTable(self::SOURCES_TABLE)],
                'main_table.id = rs.rate_id',
                ['source_codes' => new \Zend_Db_Expr('GROUP_CONCAT(rs.source)')]
            )->where('( rs.source IN (?)', $sources->getSourceCodes())
                ->orWhere('rs.source IS NULL )')
                ->group('main_table.id');
        }

        return $this;
    }

    /**
     * @param Select $select
     * @param RateRequest $request
     * @return $this
     */
    public function addAddressFilters(Select $select, RateRequest $request): self
    {
        $connection = $this->getConnection();
        $inputZip = $request->getDestPostcode();

        $select
            ->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::COUNTRY,
                    [
                        [
                            'like' => $request->getDestCountryId(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            )->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::STATE,
                    [
                        [
                            'like' => $request->getDestRegionId(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            )->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::CITY,
                    [
                        [
                            'like' => $request->getDestCity(),
                        ],
                        [
                            'eq' => '0',
                        ],
                        [
                            'eq' => '',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );

        if ($this->configProvider->getNumericZip()) {
            $this->addZipFilters($select, $request);
        } else {
            $inputZip = $this->handleZipForUk($inputZip);
            $select->where("? LIKE zip_from OR zip_from = ''", $inputZip);
        }

        return $this;
    }

    /**
     * @param Select $select
     * @param RateRequest $request
     * @return $this
     */
    public function addZipFilters(Select $select, RateRequest $request): self
    {
        $connection = $this->getConnection();
        $inputZip = $request->getDestPostcode();

        if ($inputZip == '*') {
            $inputZip = '';
        }
        $zipData = $this->helper->getDataFromZip($inputZip);
        $zipData['district'] = $zipData['district'] !== '' ? (int)$zipData['district'] : -1;

        $select
            ->where('`num_zip_from` <= ? OR `zip_from` = ""', $zipData['district'])
            ->where('`num_zip_to` >= ? OR `zip_to` = ""', $zipData['district']);

        if (!empty($zipData['area']) && preg_match(self::FIND_PATTERN, $zipData['area'])) {
            $select->where(
                $connection->prepareSqlCondition(
                    ShippingTableRateInterface::ZIP_FROM,
                    [
                        [
                            ['regexp' => '^' . $zipData['area'] . '[0-9]+'],
                            ['eq' => '']
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );
        }

        //to prefer rate with zip
        $select->order(
            [
                ShippingTableRateInterface::NUM_ZIP_FROM . ' ' . Select::SQL_DESC,
                ShippingTableRateInterface::NUM_ZIP_TO . ' ' . Select::SQL_DESC,
            ]
        );

        return $this;
    }

    /**
     * @param Select $select
     * @param array $totals
     * @param int $shippingType
     * @param RateRequest $request
     * @param bool $allowFreePromo
     * @return $this
     */
    public function addTotalsFilters(
        Select $select,
        array $totals,
        int $shippingType,
        RateRequest $request,
        bool $allowFreePromo
    ): self {
        if (!($request->getFreeShipping() && $allowFreePromo)) {
            $select
                ->where(ShippingTableRateInterface::PRICE_FROM . ' <= ?', $totals['not_free_price'])
                ->where(ShippingTableRateInterface::PRICE_TO . ' >= ?', $totals['not_free_price']);
        }

        $select
            ->where(ShippingTableRateInterface::WEIGHT_FROM . ' <= ?', $totals['not_free_weight'])
            ->where(ShippingTableRateInterface::WEIGHT_TO . ' >= ?', $totals['not_free_weight'])
            ->where(ShippingTableRateInterface::QTY_FROM . ' <= ?', $totals['not_free_qty'])
            ->where(ShippingTableRateInterface::QTY_TO . ' >= ?', $totals['not_free_qty'])
            ->where(
                $this->getConnection()->prepareSqlCondition(
                    ShippingTableRateInterface::SHIPPING_TYPE,
                    [
                        [
                            'eq' => $shippingType,
                        ],
                        [
                            'eq' => '',
                        ],
                        [
                            'eq' => '0',
                        ],
                    ]
                ),
                null,
                Select::TYPE_CONDITION
            );

        return $this;
    }

    /**
     * @param int $newMethodId
     * @param int $originalId
     *
     * @return void
     */
    public function batchDuplicateInsertions(int $newMethodId, int $originalId): void
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable())
            ->where(ShippingTableRateInterface::METHOD_ID . ' = ?', $originalId);

        $iterator = $this->queryGenerator->generate('id', $select, $this->configProvider->getBatchDuplicateSize());
        foreach ($iterator as $selectByRange) {
            $ratesRows = $select->getConnection()->fetchAll($selectByRange);
            foreach ($ratesRows as &$rateRow) {
                unset($rateRow['id']);
                $rateRow['method_id'] = $newMethodId;
            }
            $this->insertBunch($ratesRows);
        }
    }

    /**
     * @param int $value
     * @return int
     */
    public function setResourceImportFlag(int $value): int
    {
        return $this->importFlag = $value;
    }

    private function handleZipForUK(?string $zip = null): ?string
    {
        if (!empty($zip) && strlen($zip) >= 5 && strrpos($zip, ' ') === false) {
            $startZipPart = substr($zip, 0, -3);
            $endZipPart = substr($zip, strlen($zip) - 3, strlen($zip));
            $zip = $startZipPart . ' ' . $endZipPart;
        }

        return $zip;
    }
}
