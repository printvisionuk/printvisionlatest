<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;

class SaveSources
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Update sources table according to new source codes list
     *
     * @param int $rateId
     * @param int[] $sourceCodes
     * @throws \Exception
     */
    public function execute(int $rateId, array $sourceCodes): void
    {
        $connection = $this->resourceConnection->getConnection();
        $sourcesTable = $this->resourceConnection->getTableName(Rate::SOURCES_TABLE);
        $connection->delete($sourcesTable, ['rate_id = ?' => $rateId]);

        $insertArray = [];

        foreach ($sourceCodes as $code) {
            if ($code) {
                $insertArray[] = [
                    'rate_id' => $rateId,
                    'source' => $code
                ];
            }
        }

        if ($insertArray) {
            try {
                $connection->insertMultiple($sourcesTable, $insertArray);
            } catch (\Zend_Db_Exception $e) {
                throw new LocalizedException(__('One of the inserted source codes not valid.'));
            }
        }
    }
}
