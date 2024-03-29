<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Model;

use Magento\Framework\Exception\LocalizedException;
use Magenest\Barclaycard\Helper\Constant;
use Magento\Framework\HTTP\ZendClientFactory;

class BarclaycardPayment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'magenest_barclaycard';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_isInitializeNeeded = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $customerSession;
    protected $backendAuthSession;
    protected $sessionQuote;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $_messageManager;
    protected $checkoutData;
    protected $configData;
    protected $_httpClientFactory;
    protected $barclayConfig;
    protected $_logger;
    protected $encrypter;
    protected $chargeFactory;
    protected $request;
    protected $barclayDirectHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magenest\Barclaycard\Helper\ConfigData $configData,
        \Magenest\Barclaycard\Helper\Encrypter $encrypter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\Data $checkoutData,
        ZendClientFactory $clientFactory,
        \Magenest\Barclaycard\Helper\ConfigData $barclayConfig,
        \Magenest\Barclaycard\Helper\Logger $_logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\Barclaycard\Model\ChargeFactory $chargeFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magenest\Barclaycard\Helper\BarclayDirectHelper $barclayDirectHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->customerSession = $customerSession;
        $this->backendAuthSession = $backendAuthSession;
        $this->sessionQuote = $sessionQuote;
        $this->configData = $configData;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->encrypter = $encrypter;
        $this->_messageManager = $messageManager;
        $this->checkoutData = $checkoutData;
        $this->_httpClientFactory = $clientFactory;
        $this->barclayConfig = $barclayConfig;
        $this->_logger = $_logger;
        $this->storeManager = $storeManager;
        $this->chargeFactory = $chargeFactory;
        $this->request = $requestInterface;
        $this->barclayDirectHelper = $barclayDirectHelper;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_logger->debug("---------barclay HPP---------");
        return parent::assignData($data); // TODO: Change the autogenerated stub
    }

    public function initialize($paymentAction, $stateObject)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $_orderId = $order->getIncrementId();
        $this->_logger->debug("Order: ". $_orderId);
        $orderId = $this->generateOrderId($this->configData->getOrderPrefix(), $_orderId);
        $amount = $order->getBaseGrandTotal();
        $currency_code = $order->getBaseCurrencyCode();
        $operation = Constant::OPERATION_AUTHORIZE;
        if ($paymentAction == 'authorize_capture') {
            $operation = Constant::OPERATION_AUTHORIZE_CAPTURE;
        }
        $formParams = false;
        try {
            $orderDetails = $this->getSharedOrderDetails($order, $currency_code);
            $token = $this->barclayDirectHelper->getRandomString(24);
            $formParams = [
                'ORDERID' => $orderId,
                'AMOUNT' => round($amount * 100),
                'CN' => trim($orderDetails['name']),
                'EMAIL' => $orderDetails['shopperEmailAddress'],
                'OWNERADDRESS' => $orderDetails['billingAddress']['address1'],
                'OWNERTOWN' => $orderDetails['billingAddress']['city'],
                'OWNERZIP' => $orderDetails['billingAddress']['postalCode'],
                'OWNERCTY' => strtoupper($orderDetails['billingAddress']['countryCode']),
                'OWNERTELNO' => $orderDetails['billingAddress']['telephoneNumber'],
                'COM' => $this->generateOrderDescription($_orderId),
                'TITLE' => $orderDetails['orderDescription'],
                'LOGO' => $orderDetails['logo'],
                'BUTTONBGCOLOR' => $orderDetails['bg_color'],
                'BUTTONTXTCOLOR' => $orderDetails['txt_color'],
                'BGCOLOR' => $this->configData->getBgColor(),
                'TXTCOLOR' => $this->configData->getTextColor(),
                'TBLBGCOLOR' => $this->configData->getTableBgColor(),
                'TBLTXTCOLOR' => $this->configData->getTableTxtColor(),
                'FONTTYPE' => $this->configData->getFontType(),
                'LANGUAGE' => $this->configData->getPaymentLanguageCode(),
                'CURRENCY' => strtoupper($order->getBaseCurrencyCode()),
                'PSPID' => $this->configData->getPspid(),
                "ACCEPTURL" => $baseUrl."barclaycard/checkout/accept",
                "DECLINEURL" => $baseUrl."barclaycard/checkout/decline",
                "EXCEPTIONURL" => $baseUrl."barclaycard/checkout/exception",
                "CANCELURL" => $baseUrl."barclaycard/checkout/cancel",
                "OPERATION" => $operation,
                "PARAMPLUS" => $this->buildFeedbackParam($_orderId, $token)
            ];

            $this->cleanParams($formParams);
            $this->encrypter->generateHash($formParams);
//            $this->_eventManager->dispatch('cancelx');

            $this->_logger->debug(var_export($formParams, true));
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            $formParams = false;
        }
        $payment->setAdditionalInformation("barclay_form_param", json_encode($formParams));
        $payment->setAdditionalInformation("barclay_pay_action", $paymentAction);
        $payment->setAdditionalInformation("barclay_token", $token);
        return parent::initialize($paymentAction, $stateObject); // TODO: Change the autogenerated stub
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote); // TODO: Change the autogenerated stub
    }

    public function validate()
    {
        return parent::validate();
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        $this->_logger->debug("authorize");
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(true);
        $transactionId = $payment->getAdditionalInformation('transaction_id');
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setIsTransactionClosed(true);
        $payment->setIsFraudDetected(!$this->checkShaKeyValid());
        return parent::authorize($payment, $amount); // TODO: Change the autogenerated stub
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        $this->_logger->debug("capture");
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(true);
        $transactionId = $payment->getAdditionalInformation('transaction_id');
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setIsTransactionClosed(true);
        $payment->setIsFraudDetected(!$this->checkShaKeyValid());
        return parent::capture($payment, $amount); // TODO: Change the autogenerated stub
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return parent::refund($payment, $amount); // TODO: Change the autogenerated stub
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        throw new LocalizedException(__('You cannot void an Barclaycard order'));
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        throw new LocalizedException(__('You cannot cancel an Barclaycard order'));
    }

//    Function for redirecting...............

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function createFormInfo($order)
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getIncrementId();
//        $orderPrefix = $this->configData->getOrderPrefix();
//        if (!!$orderPrefix) {
//            $orderId = $orderPrefix . "_" . $orderId;
//        }
        $orderId = $this->generateOrderId($this->configData->getOrderPrefix(), $orderId);
        $amount = $order->getBaseGrandTotal();
        $currency_code = $order->getBaseCurrencyCode();
        $formParams = false;
        try {
            $orderDetails = $this->getSharedOrderDetails($order, $currency_code);

            $formParams = [
                'ORDERID' => $orderId,
                'AMOUNT' => round($amount * 100),
                'CN' => $orderDetails['name'],
                'EMAIL' => $orderDetails['shopperEmailAddress'],
                'OWNERADDRESS' => $orderDetails['billingAddress']['address1'],
                'OWNERTOWN' => $orderDetails['billingAddress']['city'],
                'OWNERZIP' => $orderDetails['billingAddress']['postalCode'],
                'OWNERCTY' => $orderDetails['billingAddress']['city'],
                'OWNERTELNO' => $orderDetails['billingAddress']['telephoneNumber'],
                'TITLE' => $orderDetails['orderDescription'],
                'LOGO' => $orderDetails['logo'],
                'BUTTONBGCOLOR' => $orderDetails['bg_color'],
                'BUTTONTXTCOLOR' => $orderDetails['txt_color'],
                'LANGUAGE' => $this->configData->getPaymentLanguageCode(),
                'CURRENCY' => strtoupper($order->getBaseCurrencyCode()),
                'PSPID' => $this->configData->getPspid()
            ];

            $this->cleanParams($formParams);
            $this->encrypter->generateHash($formParams);
            $this->_logger->debug(var_export($formParams, true));
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());

            return false;
        }

        return $formParams;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $currencyCode
     * @return array
     */
    private function getSharedOrderDetails($order, $currencyCode)
    {
        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        if ($shipping == null) {
            $shipping = $billing;
        }

        $data = [];

        $data['orderDescription'] = $this->configData->getPaymentDescription();

        if (!$data['orderDescription']) {
            $data['orderDescription'] = "Magento 2 Order";
        }

        $data['logo'] = $this->configData->getPayLogo();

        if (!$data['logo']) {
            $data['logo'] = "https://www.barclaycard.co.uk/business/images/barclaycard_logo.png";
        }

        $data['bg_color'] = $this->configData->getButtonBgColor();

        if (!$data['bg_color']) {
            $data['bg_color'] = "802626";
        }

        $data['txt_color'] = $this->configData->getButtonTextColor();

        if (!$data['txt_color']) {
            $data['txt_color'] = "FFFFFF";
        }

        $data['currencyCode'] = $currencyCode;
        $data['name'] = $billing->getFirstname()." ".$billing->getLastname();

        $data['billingAddress'] = [
            "address1" => $billing->getStreetLine(1),
            "address2" => $billing->getStreetLine(2),
            "address3" => $billing->getStreetLine(3),
            "postalCode" => $billing->getPostcode(),
            "city" => $billing->getCity(),
            "state" => "",
            "countryCode" => $billing->getCountryId(),
            "telephoneNumber" => $billing->getTelephone()
        ];

        $data['deliveryAddress'] = [
            "firstName" => $shipping->getFirstname(),
            "lastName" => $shipping->getLastname(),
            "address1" => $shipping->getStreetLine(1),
            "address2" => $shipping->getStreetLine(2),
            "address3" => $shipping->getStreetLine(3),
            "postalCode" => $shipping->getPostcode(),
            "city" => $shipping->getCity(),
            "state" => "",
            "countryCode" => $shipping->getCountryId(),
            "telephoneNumber" => $shipping->getTelephone()
        ];


        $data['shopperSessionId'] = $this->customerSession->getSessionId();
        $data['shopperUserAgent'] = !empty($this->request->getServer('HTTP_USER_AGENT')) ? $this->request->getServer('HTTP_USER_AGENT') : '';
        $data['shopperAcceptHeader'] = '*/*';

        if ($this->backendAuthSession->isLoggedIn()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $objectManager->create(\Magento\Customer\Model\Customer::class)->load($this->sessionQuote->getCustomerId());
            $data['shopperEmailAddress'] = $customer->getEmail();
        } else {
            $data['shopperEmailAddress'] = $this->customerSession->getCustomer()->getEmail();
        }
        $data['siteCode'] = null;

        return $data;
    }

    private function cleanParams(&$mFormParams)
    {
        foreach ($mFormParams as $key => $param) {
            if ($param == null || strlen($param) == 0) {
                unset($mFormParams[$key]);
            }
        }
    }

    private function generateOrderId($prefix = "", $order_id = 0)
    {
        if (!!$prefix) {
            return $prefix ."_". $order_id;
        } else {
            return $order_id;
        }
    }

    private function generateOrderDescription($order_id)
    {
        return __("Payment for order")." ".$order_id;
    }

    private function buildFeedbackParam($order_id, $tok)
    {
        return "order_id=".$order_id."&tok=".$tok;
    }

    private function checkShaKeyValid(){
        $params = $this->request->getParams();
        $shaSign = isset($params['SHASIGN'])?$params['SHASIGN']:"";
        $shaOutGen = $this->encrypter->generateHashShaOut($params);
        if (strtolower($shaSign) == strtolower($shaOutGen)) {
            return true;
        }
        return false;
    }
}
