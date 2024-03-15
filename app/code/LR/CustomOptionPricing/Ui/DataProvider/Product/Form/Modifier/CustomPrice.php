<?php
namespace LR\CustomOptionPricing\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions as CustomOptionsModifier;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;

class CustomPrice extends AbstractModifier
{
    protected $meta = [];

    public function __construct(
        UrlInterface $urlBuilder,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->locator = $locator;
        $this->storeManager = $storeManager;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->addCustomOptionsFields();

        return $this->meta;
    }

    protected function addCustomOptionsFields()
    {
        $groupCustomOptionsName = CustomOptionsModifier::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptionsModifier::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptionsModifier::CONTAINER_COMMON_NAME;

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $this->getCustomPricingField()
        );
    }

    protected function getCustomPricingField()
    {
        $fields['is_custom_pricing'] = $this->getCustomPricingFieldonfig(65);

        return $fields;
    }

    protected function getCustomPricingFieldonfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Is Custom Pricing'),
                        'componentType' => Field::NAME,
                        'formElement' => Checkbox::NAME,
                        'dataScope' => 'is_custom_pricing',
                        'dataType' => Text::NAME,
                        'prefer'        => 'toggle',
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => '0',
                        ],
                    ],
                ],
            ],
        ];
    }
}
