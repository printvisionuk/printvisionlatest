<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Block\Adminhtml\Form\Field;

class CustomerGroup extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Customer\Model\GroupFactory $groupfactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Customer\Model\GroupFactory $groupfactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_groupFactory = $groupfactory;
    }

    /**
     * Render HTML
     *
     * @return array
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $customerGroupCollection = $this->_groupFactory->create()->getCollection();
            foreach ($customerGroupCollection as $customerGroup) {
                $this->addOption($customerGroup->getCustomerGroupId(), $customerGroup->getCustomerGroupCode());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
