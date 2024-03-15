<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Block\Adminhtml\Config;

/**
 * WebConfig Class
 */
abstract class WebConfig extends \Magento\Config\Block\System\Config\Form\Field
{

    protected $helper;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */

    protected $configData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magenest\Barclaycard\Helper\ConfigData $configData,
        array $data = []
    ) {
        $this->storeFactory = $storeFactory;
        $this->websiteFactory = $websiteFactory;
        $this->configData = $configData;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Test the API connection and report common errors.
     *
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = (string)$this->webConfig();
        if (strpos($html, 'success') === false) {
            $html = '<strong>' . $html . '</strong>';
        }

        return $html;
    }

    abstract protected function webConfig();
}
