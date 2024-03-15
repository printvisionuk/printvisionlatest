<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Block\System\Config\Form\Field;

class CustomerMinimum extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var array
     */
    protected $_columns = [];

    /**
     * @var  \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CustomerGroup
     */
    protected $_customerGroupRenderer;

    /**
     * @var  \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CategoryGet
     */
    protected $_categoryRenderer;

    /**
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * @var string
     */
    protected $_addButtonLabel;
    
    /**
     * Main constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Returns renderer for customerGroup element
     *
     * @return \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CustomerGroup
     */
    protected function getCustomerGroupRenderer()
    {
        if (!$this->_customerGroupRenderer) {
            $this->_customerGroupRenderer = $this->getLayout()->createBlock(
                \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CustomerGroup::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_customerGroupRenderer;
    }

    /**
     * Returns renderer for customerGroup element
     *
     * @return \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CustomerGroup
     */
    protected function getCategoryRenderer()
    {
        if (!$this->_categoryRenderer) {
            $this->_categoryRenderer = $this->getLayout()->createBlock(
                \Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field\CategoryGet::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_categoryRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        // $this->addColumn(
        //     'category',
        //     [
        //     'label' => __('Category'),
        //     'renderer' => $this->getCategoryRenderer(),
        //     ]
        // );

        $this->addColumn(
            'customer_group',
            [
            'label' => __('Customer Group'),
            'renderer' => $this->getCustomerGroupRenderer(),
            ]
        );
        $this->addColumn('minimum_amount', ['label' => __('Minimum Amount')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $customerGroup = $row->getCustomerGroup();
        $options = [];
        if ($customerGroup) {
            $options['option_' . $this->getCustomerGroupRenderer()
            ->calcOptionHash($customerGroup)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);

        $categoryGroup = $row->getCategory();
        if ($categoryGroup) {
            $options['option_' . $this->getCategoryRenderer()->calcOptionHash($categoryGroup)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Render cell template.
     *
     * @param string $columnName
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "minimum_amount") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-number';
            $this->_columns[$columnName]['style'] = 'width:45px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
