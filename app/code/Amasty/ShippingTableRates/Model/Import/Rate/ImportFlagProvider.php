<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Import\Rate;

use Magento\Framework\FlagManager;

class ImportFlagProvider
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var int
     */
    private $flagData;

    public function __construct(FlagManager $flagManager)
    {
        $this->flagManager = $flagManager;
    }

    /**
     * @return int
     */
    public function getImportFlag(): int
    {
        if ($this->flagData === null) {
            $this->flagData = (int)$this->flagManager->getFlagData(Import::IMPORT_STATE_KEY);
        }
        return $this->flagData;
    }

    /**
     * @param int $state
     */
    public function setImportFlag(int $state): void
    {
        $this->flagManager->saveFlag(Import::IMPORT_STATE_KEY, $state);
        $this->flagData = $state;
    }
}
