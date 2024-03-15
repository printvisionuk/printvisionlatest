<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use MageWorx\OptionBase\Api\ValidatorInterface;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionFeatures\Model\QtyMultiplier;

class Validator implements ValidatorInterface
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @var QtyMultiplier
     */
    protected $qtyMultiplier;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var bool
     */
    protected $isQtyMultiplierValidationProcessed = false;

    /**
     * @param BaseHelper $baseHelper
     * @param QtyMultiplier $qtyMultiplier
     * @param ObjectManagerInterface $objectManager
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        BaseHelper $baseHelper,
        QtyMultiplier $qtyMultiplier,
        ObjectManagerInterface $objectManager,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->baseHelper            = $baseHelper;
        $this->qtyMultiplier         = $qtyMultiplier;
        $this->objectManager         = $objectManager;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockConfiguration    = $stockConfiguration;
    }

    /**
     * Run validation process for add to cart action
     *
     * @param DefaultType $subject
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function canValidateAddToCart($subject, $values)
    {
        $product    = $subject->getProduct();
        $buyRequest = $subject->getRequest();
        return $this->validateQtyMultiplier($product, $buyRequest->toArray(), $values)
            && $this->validateSelectionLimit($subject, $values);
    }

    /**
     * Run validation process for qty multiplier
     *
     * @param ProductInterface $product
     * @param array $buyRequest
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    protected function validateQtyMultiplier($product, $buyRequest, $values)
    {
        if ($this->isQtyMultiplierValidationProcessed || $product->getTypeId() === Configurable::TYPE_CODE) {
            return true;
        }

        $scopeId   = $this->stockConfiguration->getDefaultScopeId();
        $stockItem = $this->stockRegistryProvider->getStockItem(
            $product->getData($this->baseHelper->getLinkField()),
            $scopeId
        );

        if (!$stockItem->getManageStock() || $this->stockConfiguration->getBackorders()) {
            return true;
        }

        if (!$values || !is_array($values)) {
            return true;
        }

        $totalQtyMultiplierQuantity = $this->qtyMultiplier->getTotalQtyMultiplierQuantity(
            $values,
            $buyRequest,
            $product
        );

        if ($this->baseHelper->checkModuleVersion('100.3.0', '', '>=', '<', 'Magento_CatalogInventory')
            && $this->baseHelper->isModuleEnabled('Magento_InventorySalesAdminUi')
            && $this->baseHelper->checkModuleVersion('1.0.3', '', '>=', '<', 'Magento_InventorySalesAdminUi')
            && $this->baseHelper->isModuleEnabled('Magento_InventorySalesApi')
            && $this->baseHelper->checkModuleVersion('1.0.3', '', '>=', '<', 'Magento_InventorySalesApi')
        ) {
            $assignedStockIdsBySkuGetter = $this->objectManager->get(
                \Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku::class
            );
            $productSalableQtyGetter     = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface::class
            );

            $isStockFound = false;
            $stockIds     = $assignedStockIdsBySkuGetter->execute($product->getSku());
            if (count($stockIds)) {
                foreach ($stockIds as $stockId) {
                    $stockId = (int)$stockId;
                    if ($stockId === 1) {
                        $isStockFound = true;
                        break;
                    }
                }
            }
            if (!$isStockFound) {
                return true;
            }

            $stockId  = 1;
            $stockQty = $productSalableQtyGetter->execute($product->getSku(), $stockId);
        } else {
            if (!$product->getExtensionAttributes() || !$product->getExtensionAttributes()->getStockItem()) {
                return true;
            }
            $stockQty = $product->getExtensionAttributes()->getStockItem()->getQty();
        }
        $this->isQtyMultiplierValidationProcessed = true;

        if ($stockQty < $totalQtyMultiplierQuantity) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("The requested qty is not available")
            );
        }

        return true;
    }

    /**
     * Run validation process for selection limits
     *
     * @param DefaultType $subject
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    protected function validateSelectionLimit($subject, $values)
    {
        $option = $subject->getOption();
        if (isset($values[$option->getOptionId()]) && is_array($values[$option->getOptionId()])) {
            $selectionCounter = count($values[$option->getOptionId()]);
            if (!$option->getSelectionLimitFrom() && !$option->getSelectionLimitTo()) {
                return true;
            }
            if ($option->getSelectionLimitFrom() > $selectionCounter
                || ($option->getSelectionLimitTo() && $option->getSelectionLimitTo() < $selectionCounter)
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        "Please, choose required number of values for option '%1'.",
                        $option->getTitle()
                    )
                );
            }
        }
        return true;
    }

    /**
     * Run validation process for cart and checkout
     * Ignore Limit Selection validation and process magento validation without throwing error, because
     * SKU Policy independent/grouped may require to choose values for already excluded values-products
     *
     * @param ProductInterface $product
     * @param CustomOptionInterface $option
     * @return bool
     */
    public function canValidateCartCheckout($product, $option)
    {
        $buyRequest = $this->baseHelper->getInfoBuyRequest($product);
        if (!$buyRequest) {
            return true;
        }
        $values = isset($buyRequest['options']) ? $buyRequest['options'] : [];
        return $this->validateQtyMultiplier($product, $buyRequest, $values);
    }
}
