<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Block\Adminhtml;

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\View\Page\Config;
use Magento\Backend\Block\Context;

class Init extends AbstractBlock
{

    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * Function Init constructor.
     *
     * @param Context $context
     * @param Config $pageConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $pageConfig,
        array $data = []
    ) {
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $data);
    }
    
    /**
     * Function _construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->addPageAsset('Elsnertech_Christmasfestive::css/custom.css');
    }
}
