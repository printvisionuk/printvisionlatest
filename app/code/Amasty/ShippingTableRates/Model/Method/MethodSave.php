<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Method;

use Amasty\ShippingTableRates\Api\MethodSaveInterface;
use Amasty\ShippingTableRates\Api\Data\MethodInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Method;
use Magento\Framework\Exception\CouldNotSaveException;

class MethodSave implements MethodSaveInterface
{
    /**
     * @var Method
     */
    private $resource;

    public function __construct(Method $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param MethodInterface $method
     * @return MethodInterface
     * @throws CouldNotSaveException
     */
    public function execute(MethodInterface $method): MethodInterface
    {
        try {
            $this->resource->save($method);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Problems with saving method'), $e);
        }

        return $method;
    }
}
