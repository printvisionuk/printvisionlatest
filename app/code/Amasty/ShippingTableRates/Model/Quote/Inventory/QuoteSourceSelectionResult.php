<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Quote\Inventory;

use Magento\Framework\Api\AbstractSimpleObject;

class QuoteSourceSelectionResult extends AbstractSimpleObject implements QuoteSourceSelectionResultInterface
{
    public const SOURCE_CODES = 'source_codes';

    /**
     * @param array $sourceCodes
     * @return QuoteSourceSelectionResultInterface
     */
    public function setSourceCodes(array $sourceCodes): QuoteSourceSelectionResultInterface
    {
        return $this->setData(self::SOURCE_CODES, $sourceCodes);
    }

    /**
     * @return array
     */
    public function getSourceCodes(): array
    {
        return $this->_get(self::SOURCE_CODES);
    }
}
