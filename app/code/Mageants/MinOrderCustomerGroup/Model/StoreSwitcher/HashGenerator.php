<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Store\Model\StoreManagerInterface;

class HashGenerator extends \Magento\Store\Model\StoreSwitcher\HashGenerator
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $_deploymentConfig;

    /**
     * @var UrlHelper
     */
    private $_urlHelper;

    /**
     * @var UserContextInterface
     */
    private $_currentUser;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Zend\Uri\Uri
     */
    private $_zendUri;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param UrlHelper $urlHelper
     * @param UserContextInterface $currentUser
     * @param StoreManagerInterface $storeManager
     * @param \Zend\Uri\Uri $zendUri
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        UrlHelper $urlHelper,
        UserContextInterface $currentUser,
        StoreManagerInterface $storeManager,
        \Zend\Uri\Uri $zendUri
    ) {
        parent::__construct($deploymentConfig, $urlHelper, $currentUser);
        $this->_deploymentConfig = $deploymentConfig;
        $this->_urlHelper        = $urlHelper;
        $this->_currentUser      = $currentUser;
        $this->_storeManager     = $storeManager;
        $this->_zendUri          = $zendUri;
    }

    /**
     * Builds redirect url with token
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        $customerId = null;
        $encodedUrl = $this->_urlHelper->getEncodedUrl($redirectUrl);

        if ($this->_currentUser->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = $this->_currentUser->getUserId();
        }
        if ($customerId) {
            $urlParts = $this->_zendUri->parse_url($targetUrl);
            $host = $urlParts['host'];
            $scheme = $urlParts['scheme'];
            $key = (string)$this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
            $timeStamp = time();
            $fromStoreCode = $fromStore->getCode();
            $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
            $signature = hash_hmac('sha256', $data, $key);
            $storeBaseURL = $this->_storeManager->getStore()->getBaseUrl();
            $targetUrl = $storeBaseURL. '/stores/store/switchrequest';
            $targetUrl = $this->_urlHelper->addRequestParam(
                $targetUrl,
                ['customer_id' => $customerId]
            );
            $targetUrl = $this->_urlHelper->addRequestParam($targetUrl, ['time_stamp' => $timeStamp]);
            $targetUrl = $this->_urlHelper->addRequestParam($targetUrl, ['signature' => $signature]);
            $targetUrl = $this->_urlHelper->addRequestParam($targetUrl, ['___from_store' => $fromStoreCode]);
            $targetUrl = $this->_urlHelper->addRequestParam(
                $targetUrl,
                [ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl]
            );
        }
        return $targetUrl;
    }
}
