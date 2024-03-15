<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Quote\Inventory;

interface QuoteSourceSelectionResultInterface
{
    /**
     * @param string[] $sourceCodes
     * @return $this
     */
    public function setSourceCodes(array $sourceCodes): self;

    /**
     * Contains a list of unique source codes that are used in specified quote.
     *
     * @return string[]
     */
    public function getSourceCodes(): array;
}
