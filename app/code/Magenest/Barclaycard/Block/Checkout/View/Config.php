<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Block\Checkout\View;

class Config extends \Magento\Framework\View\Element\Template
{
    protected $_config;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magenest\Barclaycard\Helper\ConfigData $config,
        array $data
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    public function isActive()
    {
        return $this->_config->isActive();
    }

    public function getDirectPaymentLink()
    {
        return $this->_config->getDirectPayUrl();
    }

    public function is3Ds()
    {
        return $this->_config->is3Ds();
    }
}
