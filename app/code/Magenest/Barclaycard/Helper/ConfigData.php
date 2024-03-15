<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigData extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->_encryptor = $encryptor;
    }

    public function isActive()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPspid()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/pspid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPassword()
    {
        return $this->_encryptor->decrypt($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getShaIn()
    {
        return $this->_encryptor->decrypt($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/sha_in_phrase',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getShaOut()
    {
        return $this->_encryptor->decrypt($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/sha_out_phrase',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getSecretKey()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/random_secret_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPayUrl()
    {
        $isTest = $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isTest) {
            return "https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp";
        } else {
            return "https://payments.epdq.co.uk/ncol/prod/orderstandard.asp";
        }
    }

    public function getMaintenanceUrl()
    {
        $isTest = $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isTest) {
            return "https://mdepayments.epdq.co.uk/ncol/test/maintenancedirect.asp";
        } else {
            return "https://payments.epdq.co.uk/ncol/prod/maintenancedirect.asp";
        }
    }

    public function getPaymentDescription()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCurrencyCode()
    {
        return trim(strtoupper($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/currency',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )));
    }

    public function getPaymentLanguageCode()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/language_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getUserId()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/userid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPaymentAction()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getHashMethod()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/hash_algorithm',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPayLogo()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getButtonBgColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/btn_bg_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getButtonTextColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/btn_txt_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getBgColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/bg_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTextColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/txt_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTableBgColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/tbl_bg_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTableTxtColor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/tbl_txt_color',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getFontType()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_barclaycard/font_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isLoggerActive()
    {
        return 1;
    }

//    Barclaycard direct link system option

    public function isDirectLinkActive()
    {
        return $this->scopeConfig->getValue(
            'payment/barclaycard_direct/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isUseCcv()
    {
        return $this->scopeConfig->getValue(
            'payment/barclaycard_direct/useccv',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getDirectPayUrl()
    {
        $isTest = $this->scopeConfig->getValue(
            'payment/barclaycard_direct/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isTest) {
            return "https://mdepayments.epdq.co.uk/ncol/test/orderdirect.asp";
        } else {
            return "https://payments.epdq.co.uk/ncol/prod/orderdirect.asp";
        }
    }

    public function getDirectMaintenanceUrl()
    {
        $isTest = $this->scopeConfig->getValue(
            'payment/barclaycard_direct/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isTest) {
            return "https://mdepayments.epdq.co.uk/ncol/test/maintenancedirect.asp";
        } else {
            return "https://payments.epdq.co.uk/ncol/prod/maintenancedirect.asp";
        }
    }

    public function getPaymentActionDirect()
    {
        return $this->scopeConfig->getValue(
            'payment/barclaycard_direct/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getShaInDirect()
    {
        return $this->_encryptor->decrypt($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/sha_in_phrase_direct',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getOrderPrefix()
    {
        return trim($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/order_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function is3Ds()
    {
        return trim($this->scopeConfig->getValue(
            'payment/barclaycard_direct/threeds',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getOrderExpireMin()
    {
        $minutes = (int)($this->scopeConfig->getValue(
            'payment/magenest_barclaycard/order_expire_min',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        if ($minutes<5) {
            $minutes = 5;
        }
        return $minutes;
    }
}
