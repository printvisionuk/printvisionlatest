<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Magento\Framework\App\ResourceConnection;

/**
 * Rates Resource Collection
 */
class StateValidator
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string|int $state
     * @param string|int $country
     *
     * @return bool
     */
    public function validateState($state, $country): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region'))
            ->where('region_id = ? OR default_name = ?', $state)
            ->where('country_id = ?', $country);

        return (bool)$connection->fetchOne($select);
    }
}
