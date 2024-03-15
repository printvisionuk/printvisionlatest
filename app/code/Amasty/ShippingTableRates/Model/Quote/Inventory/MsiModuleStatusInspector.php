<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Quote\Inventory;

use Magento\Framework\Module\Manager;

class MsiModuleStatusInspector
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }
}
