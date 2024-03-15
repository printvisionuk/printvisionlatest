<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Method;

use Amasty\ShippingTableRates\Api\Data\MethodInterface;
use Amasty\ShippingTableRates\Model\ConfigProvider;

class DuplicateProcessor
{
    /**
     * @var MethodSave
     */
    private $methodSave;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        MethodSave $methodSave,
        ConfigProvider $configProvider
    ) {
        $this->methodSave = $methodSave;
        $this->configProvider = $configProvider;
    }

    /**
     * @param MethodInterface $method
     */
    public function execute(MethodInterface $method): void
    {
        $method->setId(null);
        $method->setName($method->getName() . '-' . $this->configProvider->getBatchDuplicateMethodNamePostfix());
        $method->setIsActive($this->configProvider->getStatusForDuplicateMethod());

        /** @see \Amasty\ShippingTableRates\Observer\Admin\Method\Save */
        $method->setIsDuplicateMethod(true);
        $this->methodSave->execute($method);
    }
}
