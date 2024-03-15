<?php
namespace LR\CustomOptionPricing\Ui\DataProvider\Product\Form\Modifier;

interface ModifierInterface
{
    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly();

    /**
     * Get sort order of modifier to load modifiers in the right order
     *
     * @return int
     */
    public function getSortOrder();
}
