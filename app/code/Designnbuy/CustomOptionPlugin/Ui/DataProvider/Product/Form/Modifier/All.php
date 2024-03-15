<?php
/**
 * Copyright © 2020 Design'N'Buy (inquiry@designnbuy.com). All rights reserved.
 * 
 */

namespace Designnbuy\CustomOptionPlugin\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Designnbuy\CustomOptionPlugin\Helper\Data as Helper;

class All extends AbstractModifier
{
    const FIELD_QUANTITY_NAME = 'designtool_title';
    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    protected $arrayManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $meta = [];

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function modifyData(array $data)
    {
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->addDesignToolType();
        $this->addDesignToolTitle();
        return $this->meta;
    }

    /**
     * Adds features fields to the meta-data
     */
    protected function addDesignToolType()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;
        
        // Add fields to the option
        $optionFeaturesFields = $this->getOptionFeaturesFieldsConfig();
        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'],
            $optionFeaturesFields
        );
    }

    /**
     * The custom option fields config
     *
     * @return array
     */
    protected function getOptionFeaturesFieldsConfig()
    {
        $fields = [];
        $fields['designtool_type'] = $this->getDesignToolTypeConfig(60);
        return $fields;
    }


   /**
     * Enable qty input (for option) field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getDesignToolTypeConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Designtool Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => 'designtool_type',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
						'options' => $this->helper->getDesigntoolTypeOptions(),
                        'validation' => [
                            'required-entry' => false
                        ],
                    ],
                ],
            ],
        ];
    }
	
	

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return false;
    }
    
    protected function addDesignToolTitle()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $designToolTitleFields = $this->getDesignToolTitleFields();

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children']['values']['children']['record']['children'],
            $designToolTitleFields
        );
    }
    /**
     * Create additional custom options fields
     *
     * @return array
     */
    protected function getDesignToolTitleFields()
    {
        $fields = [
            'designtool_title' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('DesignTool Title'),
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'dataScope' => static::FIELD_QUANTITY_NAME,
                            'dataType' => Text::NAME,
                            'fit' => true,
                            'validation' => [
                                //'validate-text' => true,
                                'required-entry' => false
                            ],
                            'sortOrder' => 140,
                        ],
                        'imports' => [
                            'seeminglyArbitraryValue' => '${ $.provider }:data.form_id_field_name',
                        ],
                        'exports' => [
                            'seeminglyArbitraryValue' => '${ $.externalProvider }:params.form_id_field_name',
                        ],
                    ],
                ],
            ],
            
        ];

        return $fields;
    }
}
