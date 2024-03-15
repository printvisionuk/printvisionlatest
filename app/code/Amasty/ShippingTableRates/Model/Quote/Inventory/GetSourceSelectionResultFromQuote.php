<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Quote\Inventory;

use Amasty\ShippingTableRates\Model\ConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Quote\Model\Quote;

class GetSourceSelectionResultFromQuote
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var InventoryRequestFromQuoteFactory
     */
    private $inventoryRequestFromQuoteFactory;

    /**
     * @var QuoteSourceSelectionResultInterfaceFactory
     */
    private $quoteSourceSelectionResultFactory;

    /**
     * @var array<int, QuoteSourceSelectionResultInterface>
     */
    private $cachedResults = [];

    /**
     * @var MsiModuleStatusInspector
     */
    private $msiModuleStatusInspector;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ConfigProvider $configProvider,
        InventoryRequestFromQuoteFactory $inventoryRequestFromQuoteFactory,
        QuoteSourceSelectionResultInterfaceFactory $quoteSourceSelectionResultFactory,
        ObjectManagerInterface $objectManager,
        MsiModuleStatusInspector $msiModuleStatusInspector
    ) {
        $this->inventoryRequestFromQuoteFactory = $inventoryRequestFromQuoteFactory;
        $this->quoteSourceSelectionResultFactory = $quoteSourceSelectionResultFactory;
        $this->configProvider = $configProvider;
        $this->msiModuleStatusInspector = $msiModuleStatusInspector;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Quote $quote
     * @param bool $useCache
     * @return QuoteSourceSelectionResultInterface
     * @throws NoSuchEntityException
     */
    public function execute(Quote $quote, bool $useCache = true): QuoteSourceSelectionResultInterface
    {
        if ($this->msiModuleStatusInspector->isEnabled()) {
            $sourceSelectionService = $this->objectManager->create(
                \Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface::class
            );
        }

        if ($useCache && $cachedResult = $this->cachedResults[(int) $quote->getId()] ?? null) {
            return $cachedResult;
        }

        $inventoryRequest = $this->inventoryRequestFromQuoteFactory->create($quote);
        $selectionAlgorithmCode = $this->configProvider->getMsiAlgorithm();
        $sourceSelectionResult = $sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);
        $quoteSourceSelectionResult = $this->convertResult($sourceSelectionResult);

        if ($useCache) {
            $this->cachedResults[(int) $quote->getId()] = $quoteSourceSelectionResult;
        }

        return $quoteSourceSelectionResult;
    }

    /**
     * @param SourceSelectionResultInterface $sourceSelectionResult
     * @return QuoteSourceSelectionResultInterface
     */
    private function convertResult(
        SourceSelectionResultInterface $sourceSelectionResult
    ): QuoteSourceSelectionResultInterface {
        $sourceCodes = [];

        foreach ($sourceSelectionResult->getSourceSelectionItems() as $sourceSelectionItem) {
            if ($sourceSelectionItem->getQtyToDeduct()) {
                $sourceCodes[$sourceSelectionItem->getSku()][] = $sourceSelectionItem->getSourceCode();
            }
        }

        $sources = [];
        foreach ($sourceCodes as $sourceCodePerItem) {
            if (!empty($sources)) {
                $sources = array_intersect($sources, $sourceCodePerItem);
            } else {
                $sources = $sourceCodePerItem;
            }
        }

        $sources = array_unique($sources);

        return $this->quoteSourceSelectionResultFactory->create()
            ->setSourceCodes($sources);
    }
}
