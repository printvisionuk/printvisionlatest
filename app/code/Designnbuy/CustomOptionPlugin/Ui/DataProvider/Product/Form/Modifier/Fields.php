<?php
/**
 * Copyright © 2020 Design'N'Buy (inquiry@designnbuy.com). All rights reserved.
 * 
 */
namespace Designnbuy\CustomOptionPlugin\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;

class Fields extends AbstractModifier /*implements \MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier\ModifierInterface*/
{
    const FIELD_QUANTITY_NAME = 'designtool_title';
    const FIELD_UPLOAD_PRICING = 'use_in_upload';

    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    
    /**
     * @var CoreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager,
        Registry $registry,
        AttributeSetRepositoryInterface $attributeSet
    ) {
        $this->arrayManager = $arrayManager;
        $this->_coreRegistry = $registry;
        $this->attributeSet = $attributeSet;
    }

    /**
     * {@inheritdoc}
     */
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
        
        $this->addDesignToolTypeFields();
        //echo "<pre>"; print_r($this->meta); exit;
        return $this->meta;
    }

    protected function addDesignToolTypeFields()
    {
        $groupCustomOptionsName =
            \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $designToolTypeFields = $this->getDesignToolTypeFields();

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        ['container_option']['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children']['values']['children']['record']['children'],
            $designToolTypeFields
        );
    }

    /**
     * Create additional custom options fields
     *
     * @return array
     */
    protected function getDesignToolTypeFields()
    {
        $fields = [
            'designtool_title' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('DesignTool Title'),
                            'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                            'formElement' => \Magento\Ui\Component\Form\Element\Input::NAME,
                            'dataScope' => static::FIELD_QUANTITY_NAME,
                            'dataType' => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
                            'fit' => true,
                            'validation' => [
                                //'validate-text' => true,
                                'required-entry' => false
                            ],
                            'sortOrder' => 140,
                            'visible' => false,

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
        if($this->_coreRegistry->registry('current_product')->getAttributeSetId()){

            $attrName = $this->attributeSet->get($this->_coreRegistry->registry('current_product')->getAttributeSetId())->getAttributeSetName();
            
            if ($attrName == 'CustomProduct' || $attrName == 'CustomPrint') {
                $fields['designtool_title']['arguments']['data']['config']['visible'] = true;
            }
        
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
