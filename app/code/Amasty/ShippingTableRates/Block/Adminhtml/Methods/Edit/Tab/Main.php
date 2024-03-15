<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab;

use Amasty\ShippingTableRates\Model\Rate;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Escaper;

/**
 * General Tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var \Amasty\ShippingTableRates\Model\Config\Source\Statuses
     */
    private $statuses;

    /**
     * @var \Amasty\ShippingTableRates\Helper\Data
     */
    private $helper;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\ShippingTableRates\Model\Config\Source\Statuses $statuses,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        Escaper $escaper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->statuses = $statuses;
        $this->escaper = $escaper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('General');
    }

    public function getTabTitle()
    {
        return __('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        /** @var \Amasty\ShippingTableRates\Api\Data\MethodInterface $model */
        $model = $this->_coreRegistry->registry('current_amasty_table_method');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amstrates_');
        $fieldsetGeneral = $form->addFieldset('general_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldsetGeneral->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldsetGeneral->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'note' => __(
                    'Specify either a general name for the shipping method or assign a unique name to each rate. '
                    . 'If you’re interested in the latter option, please insert variable {name} in the field above '
                    . 'to retrieve the value from the ‘Name Delivery’ column located in the \'Methods and Rates\' tab. '
                    . 'You can also add variable {day} to display the delivery time information from column '
                    . '‘Estimated Delivery (days)’.'
                )
            ]
        );

        $fieldsetGeneral->addField(
            'free_types',
            'multiselect',
            [
                'name' => 'free_types',
                'label' => __('Ship These Shipping Types for Free'),
                'title' => __('Ship These Shipping Types for Free'),
                'values' => $this->helper->getTypes(),
                'note' => __(
                    'In the cart with multiple shipping types, the products with the selected shipping types will be '
                    . 'delivered for free if there are no rates applicable to them.'
                )
            ]
        );

        $fieldsetGeneral->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'note' => $this->escaper->escapeHtml(__('Specify a comment for your shipping method if necessary. 
                    Use HTML tags to make the message catchy. The following tags <b>, <u>, <i>, <s> are supported. 
                    For example: This is a <b>Bold text</b>. To learn more, refer to '))
                    . '<a href="' . $this->escaper->escapeUrl('https://www.w3schools.com/html/html_css.asp')
                    . '" title="' . __('HTML Styles - CSS')
                    . '" target="_blank">' . 'this page' . '</a>' . '.'
            ]
        );

         $fieldsetGeneral->addField(
             'comment_img',
             'image',
             [
                'name' => 'comment_img',
                'label' => __('Image'),
                'title' => __('Image'),
                'note' => __(
                    'Upload an image to the shipping method and insert it anywhere in the Comment field '
                    . 'with the {IMG} variable.'
                )
             ]
         );

        $fieldsetGeneral->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Status'),
                'title' => __('Status'),
                'options' => $this->statuses->toOptionArray(),
            ]
        );

        $fieldsetGeneral->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRates = $form->addFieldset('rates_fieldset', ['legend' => __('Rates')]);

        $fieldsetRates->addField(
            'min_rate',
            'text',
            [
                'name' => 'min_rate',
                'label' => __('Minimal rate'),
                'title' => __('Minimal rate'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRates->addField(
            'max_rate',
            'text',
            [
                'name' => 'max_rate',
                'label' => __('Maximal rate'),
                'title' => __('Maximal rate'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRates->addField(
            'select_rate',
            'select',
            [
                'name' => 'select_rate',
                'label' => __('For products with different shipping types'),
                'title' => __('For products with different shipping types'),
                'values' => [
                    [
                        'value' => Rate::ALGORITHM_SUM,
                        'label' => __('Sum up rates')
                    ],
                    [
                        'value' => Rate::ALGORITHM_MAX,
                        'label' => __('Select maximal rate')
                    ],
                    [
                        'value' => Rate::ALGORITHM_MIN,
                        'label' => __('Select minimal rate')
                    ]
                ]
            ]
        );

        $fieldsetRates->addField(
            'weight_type',
            'select',
            [
                'name' => 'weight_type',
                'label' => __('Weight Type For Calculation'),
                'value' => Rate::WEIGHT_TYPE_MAX,
                'values' => [
                    [
                        'value' => Rate::WEIGHT_TYPE_VOLUMETRIC,
                        'label' => __('Volumetric Weight')
                    ],
                    [
                        'value' => Rate::WEIGHT_TYPE_WEIGHT,
                        'label' => __('Weight')
                    ],
                    [
                        'value' => Rate::WEIGHT_TYPE_MAX,
                        'label' => __('Max of V. Weight and Weight'),
                        'selected' => true
                    ],
                    [
                        'value' => Rate::WEIGHT_TYPE_MIN,
                        'label' => __('Min of V. Weight and Weight')
                    ]
                ]
            ]
        );

        if (!$model->getWeightType()) {
            $model->setWeightType(Rate::WEIGHT_TYPE_MAX);
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
