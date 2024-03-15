<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Api;

interface MethodDeleteInterface
{
    /**
     * @param \Amasty\ShippingTableRates\Api\Data\MethodInterface $method
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(\Amasty\ShippingTableRates\Api\Data\MethodInterface $method): bool;
}
