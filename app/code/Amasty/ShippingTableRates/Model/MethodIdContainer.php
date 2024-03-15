<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model;

class MethodIdContainer
{
    /**
     * @var int|null
     */
    private $methodId;

    /**
     * @return int|null
     */
    public function getMethodId(): ?int
    {
        return $this->methodId;
    }

    /**
     * @param int $methodId
     */
    public function setMethodId(int $methodId): void
    {
        $this->methodId = $methodId;
    }
}
