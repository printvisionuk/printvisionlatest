<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel\Rate;

use Amasty\ShippingTableRates\Model\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate as RateResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Rates Resource Collection
 */
class Collection extends AbstractCollection
{
    public const JOIN_SOURCE_FLAG = 'join_source';

    protected function _construct()
    {
        $this->_init(Rate::class, RateResource::class);
    }

    /**
     * @return $this|Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinSources();

        return $this;
    }

    /**
     * @return $this
     */
    public function joinSources(): Collection
    {
        if ($this->getFlag(self::JOIN_SOURCE_FLAG)) {
            return $this;
        }
        $this->setFlag(self::JOIN_SOURCE_FLAG, 1);
        $this->getSelect()->joinLeft(
            ['rs' => $this->getTable(RateResource::SOURCES_TABLE)],
            'main_table.id = rs.rate_id',
            ['source_codes' => new \Zend_Db_Expr('GROUP_CONCAT(rs.source)')]
        );
        $this->getSelect()->group('main_table.id');

        return $this;
    }
}
