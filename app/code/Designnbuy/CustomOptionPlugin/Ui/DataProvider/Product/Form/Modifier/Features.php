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
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class Features extends AbstractModifier
{
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
     * @var CoreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var array
     */
    protected $meta = [];

    public function __construct(
        Helper $helper,
        Registry $registry,
        AttributeSetRepositoryInterface $attributeSet
    ) {
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        $this->attributeSet = $attributeSet;
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
        $this->addFeaturesFields();
        return $this->meta;
    }

    /**
     * Adds features fields to the meta-data
     */
    protected function addFeaturesFields()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName = CustomOptions::CONTAINER_OPTION;
        $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;
        
        if(isset($this->meta[$groupCustomOptionsName])){
            // Add fields to the option
            $optionFeaturesFields = $this->getOptionFeaturesFieldsConfig();
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
                $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
                [$optionContainerName]['children'][$commonOptionContainerName]['children'],
                $optionFeaturesFields
            );
        }
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
        $fields = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('DesignTool Type'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => 'designtool_type',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
						'options' => $this->helper->getDesigntoolTypeOptions(),
                        'validation' => [
                            'required-entry' => false
                        ],
                        'visible' => false,
                    ],
                ],
            ],
        ];
        if($this->_coreRegistry->registry('current_product')->getAttributeSetId()){

            $attrName = $this->attributeSet->get($this->_coreRegistry->registry('current_product')->getAttributeSetId())->getAttributeSetName();
            
            //if ($attrName == 'CustomProduct' || $attrName == 'CustomPrint') {
                $fields['arguments']['data']['config']['visible'] = true;
            //}
        
        }
        return $fields;
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
}
