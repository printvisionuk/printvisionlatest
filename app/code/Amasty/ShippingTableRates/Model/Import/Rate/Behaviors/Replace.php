<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Import\Rate\Behaviors;

use Amasty\Base\Model\Import\Behavior\BehaviorInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\Import;
use Amasty\ShippingTableRates\Model\Import\Rate\ImportFlagProvider;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\TableMaintainer;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\FlagManager;

/**
 * Our replacement behavior means that all old records will be removed and new records will be added
 */
class Replace implements BehaviorInterface
{
    /**
     * @var Add
     */
    private $addBehavior;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var bool
     */
    private $isDeleted = false;

    /**
     * @var ImportFlagProvider
     */
    private $importFlagProvider;

    public function __construct(
        Request $request,
        Add $addBehavior,
        FlagManager $flagManager,
        TableMaintainer $tableMaintainer,
        ImportFlagProvider $importFlagProvider
    ) {
        $this->request = $request;
        $this->addBehavior = $addBehavior;
        $this->flagManager = $flagManager;
        $this->tableMaintainer = $tableMaintainer;
        $this->importFlagProvider = $importFlagProvider;
    }

    /**
     * @param array $importData
     * @return \Magento\Framework\DataObject
     */
    public function execute(array $importData)
    {
        $shippingMethodId = (int)$this->request->getPost('amastrate_method');
        $replicaTableName = $this->tableMaintainer->getReplicaTable(Rate::MAIN_TABLE);
        $count = 0;

        $this->tableMaintainer->clearTable($replicaTableName);
        $this->tableMaintainer->copyDataToReplicaTable(Rate::MAIN_TABLE);

        $this->importFlagProvider->setImportFlag(Import::STATE_ACTIVE);

        if (!$this->isDeleted) {
            $count = $this->tableMaintainer->getRateCountByMethodId(Rate::MAIN_TABLE, $shippingMethodId);
            $this->tableMaintainer->clearTableByMethodId(Rate::MAIN_TABLE, $shippingMethodId);
            $this->isDeleted = true;
        }
        $resultImport = $this->addBehavior->execute($importData);
        $this->importFlagProvider->setImportFlag(Import::STATE_INACTIVE);

        $resultImport->setCountItemsDeleted($resultImport->getCountItemsDeleted() + $count);
        $this->tableMaintainer->clearTable($replicaTableName);

        return $resultImport;
    }
}
