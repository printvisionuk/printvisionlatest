<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Config\Source;

use Amasty\ShippingTableRates\Model\Quote\Inventory\MsiModuleStatusInspector;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManagerInterface;

class MsiAlgorithm implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MsiModuleStatusInspector
     */
    private $msiModuleStatusInspector;

    public function __construct(
        ObjectManagerInterface $objectManager,
        MsiModuleStatusInspector $msiModuleStatusInspector
    ) {
        $this->objectManager = $objectManager;
        $this->msiModuleStatusInspector = $msiModuleStatusInspector;
    }

    public function toOptionArray(): array
    {
        if ($this->msiModuleStatusInspector->isEnabled()) {
            $getSourceSelectionAlgorithmList = $this->objectManager->create(
                \Magento\InventorySourceSelectionApi\Model\GetSourceSelectionAlgorithmList::class
            );
            if ($this->options === null) {
                $this->options = [];

                foreach ($getSourceSelectionAlgorithmList->execute() as $selectionAlgorithm) {
                    $this->options[] = [
                        'value' => $selectionAlgorithm->getCode(),
                        'label' => $selectionAlgorithm->getTitle()
                    ];
                }
            }

            return $this->options;
        }

        return [];
    }
}
