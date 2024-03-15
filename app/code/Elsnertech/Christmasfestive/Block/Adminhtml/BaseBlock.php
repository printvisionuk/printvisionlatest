<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Block\Adminhtml;

use Magento\Framework\UrlFactory;
use Elsnertech\Christmasfestive\Block\Adminhtml\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class BaseBlock extends Template
{
    /**
     * @var Data
     */
    protected $_devToolHelper;

    /**
     * @var Url
     */
    protected $_urlApp;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * Function __construct
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->_devToolHelper = $context->getChristmasfestiveHelper();
        $this->_config = $context->getConfig();
        $this->_urlApp=$context->getUrlFactory()->create();
        parent::__construct($context);
    }

    /**
     * Function check get EventDetails
     *
     * @return void
     */
    public function getEventDetails()
    {
        return  $this->_devToolHelper->getEventDetails();
    }

    /**
     * Function check get CurrentUrl
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlApp->getCurrentUrl();
    }

    /**
     * Function check get ControllerUrl
     *
     * @param string $routePath
     * @return void
     */
    public function getControllerUrl($routePath)
    {
        return $this->_urlApp->getUrl($routePath);
    }

   /**
    * Function getConfigValue
    *
    * @param string $path
    * @return void
    */
    public function getConfigValue($path)
    {
        return $this->_config->getCurrentStoreConfigValue($path);
    }

    /**
     * Function check remote address canShowChristmasfestive
     *
     * @return boolean
     */
    public function canShowChristmasfestive()
    {

        $remote = $this->_context->getRemoteAddress();
        $remoteIp=$remote->getRemoteAddress();
        $isEnabled=$this->getConfigValue('christmasfestive/module/is_enabled');
        if ($isEnabled) {
            $allowedIps=$this->getConfigValue('christmasfestive/module/allowed_ip');
            if ($var===null($allowedIps)) {
                return true;
            } else {
                if (strpos($allowedIps, $remoteIp) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
