<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magenest\Barclaycard\Helper\Constant;
use Magento\Sales\Model\Order;

class BarclayDirect extends \Magento\Payment\Model\Method\Cc
{
    protected $customerSession;
    protected $backendAuthSession;
    protected $sessionQuote;
    protected $checkoutSession;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $encrypter;
    protected $_messageManager;
    protected $configData;
    protected $barclayDirectHelper;
    protected $_storeManagerInterface;
    protected $_logger;
    protected $chargeFactory;
    protected $request;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magenest\Barclaycard\Helper\ConfigData $configData,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magenest\Barclaycard\Helper\Encrypter $encrypter,
        \Magenest\Barclaycard\Helper\BarclayDirectHelper $barclayDirectHelper,
        \Magenest\Barclaycard\Helper\Logger $_logger,
        \Magenest\Barclaycard\Model\ChargeFactory $chargeFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
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
        $this->_messageManager = $messageManager;
        $this->barclayDirectHelper = $barclayDirectHelper;
        $this->encrypter = $encrypter;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_logger = $_logger;
        $this->chargeFactory = $chargeFactory;
        $this->request = $requestInterface;
    }

    protected $checkoutData;

    const CODE = 'barclaycard_direct';

    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canSaveCc = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_debugReplacePrivateDataKeys = [
        'CARDNO',
        'ED',
        'CVC',
        'PSWD'
    ];
    protected $_isInitializeNeeded = true;

    public function initialize($paymentAction, $stateObject)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $orderStatus = $this->getConfigData('order_status');
//        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
//        $stateObject->setIsNotified(false);

            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
        $isCustomerNotified = $order->getCustomerNoteNotify();
            $amount = $order->getBaseGrandTotal();
            $expYear = $payment->getCcExpYear();
            $expMonth = $payment->getCcExpMonth();
            $date = date_create_from_format("d-n-Y", "01-$expMonth-$expYear");
            $time = $date->format("my");
            $orderId = $this->generateOrderId($this->configData->getOrderPrefix(), $order->getIncrementId());
        if ($paymentAction == 'authorize') {
            $operation = Constant::OPERATION_AUTHORIZE;
        } else {
            $operation = Constant::OPERATION_AUTHORIZE_CAPTURE;
        }
            $orderDetails = $this->getSharedOrderDetails($order, null);
            $data = [
                'PSPID' => $this->configData->getPspid(),
                'ORDERID' => $orderId,
                'COM' => $this->generateOrderDescription($order->getIncrementId()),
                'CN' => trim($orderDetails['name']),
                'EMAIL' => $order->getCustomerEmail(),
                'USERID' => $this->configData->getUserId(),
                'PSWD' => $this->configData->getPassword(),
                'AMOUNT' => (string)round($amount * 100),
                'CURRENCY' => strtoupper($order->getBaseCurrencyCode()),
                'CARDNO' => $payment->getCcNumber(),
                'ED' => $time,
                'CVC' => $payment->getCcCid(),
                'OWNERADDRESS' => $orderDetails['billingAddress']['address1'],
                'OWNERTOWN' => $orderDetails['billingAddress']['city'],
                'OWNERZIP' => $orderDetails['billingAddress']['postalCode'],
                'OWNERCTY' => strtoupper($orderDetails['billingAddress']['countryCode']),
                'OWNERTELNO' => $orderDetails['billingAddress']['telephoneNumber'],
                'OPERATION' => $operation,
                'ECI' => "7"
            ];
            if ($order->getRemoteIp()) {
                $data['REMOTE_ADDR'] = $order->getRemoteIp();
            }

            if ($this->_appState->getAreaCode()) {
                $has3ds = $this->configData->is3Ds();
                if ($has3ds) {
                    //add for d3d
                    $token = $this->barclayDirectHelper->getRandomString(24);
                    $payment->setAdditionalInformation("barclay_token_encrypt");
                    $data['FLAG3D'] = 'Y';
                    $data['WIN3DS'] = 'MAINW';
                    $data['HTTP_ACCEPT'] = $this->request->getServer('HTTP_ACCEPT');
                    $data['HTTP_USER_AGENT'] = $this->request->getServer('HTTP_USER_AGENT');

                    $baseUrl = $this->_storeManagerInterface->getStore()->getBaseUrl();
                    $data['ACCEPTURL'] = $baseUrl . "barclaycard/checkout/accept";
                    $data['DECLINEURL'] = $baseUrl . "barclaycard/checkout/decline";
                    $data['EXCEPTIONURL'] = $baseUrl . "barclaycard/checkout/exception";
                    $data['PARAMPLUS'] = $this->buildFeedbackParam($order->getIncrementId(), $token);
                    $data['COMPLUS'] = "3d secure";
                    $data['LANGUAGE'] = 'en_US';
                    $payment->setAdditionalInformation("barclay_token", $token);
                }
            }
            $this->_debug($data);
            $resultXml = $this->barclayDirectHelper->performPayment($data);
            $this->_logger->debug("raw response: " . $resultXml);
            if (!!$resultXml) {
                $xmlResp = simplexml_load_string($resultXml);
                $xmlArr = (array) $xmlResp;
                $atts_array = isset($xmlArr['@attributes'])?$xmlArr['@attributes']:[];
                $orderId = isset($atts_array['orderID']) ? $atts_array['orderID'] : "";
                $payId = isset($atts_array['PAYID']) ? $atts_array['PAYID'] : "";
                $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
                $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
                $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";
                if ($ncError == "0") {
                    if ($status == "46") {
                        //3d secure require
                        $order->setCanSendNewEmailFlag(false);
                        $htmlEncoded = isset($xmlArr['HTML_ANSWER'])?$xmlArr['HTML_ANSWER']:"";
                        $payment->setAdditionalInformation("barclay_pay_action", $paymentAction);
                        $payment->setAdditionalInformation(Constant::HAS3DS, true);
                        $payment->setAdditionalInformation(Constant::THREEDS_CODE, $htmlEncoded);
                        return parent::initialize($paymentAction, $stateObject);
                    }
                    //payment success
                    $payment->setAdditionalInformation("barclay_response_data", json_encode($atts_array));
                    $totalDue = $order->getTotalDue();
                    $baseTotalDue = $order->getBaseTotalDue();
                    $orderState = Order::STATE_PROCESSING;
                    if ($paymentAction == 'authorize') {
                        $payment->authorize(true, $baseTotalDue);
                        $payment->setAmountAuthorized($totalDue);
                    } else {
                        $payment->setAmountAuthorized($totalDue);
                        $payment->setBaseAmountAuthorized($baseTotalDue);
                        $payment->capture(null);
                    }
                    $orderState = $order->getState() ? $order->getState() : $orderState;
                    $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
                    $stateObject->setState($orderState);
                    $stateObject->setStatus($orderStatus);
                } else {
                    $errMsg = 'Something went wrong. Please try again later.';
                    if ($ncerrorPlus) {
                        $errMsg = $ncerrorPlus;
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($errMsg)
                    );
                }
            } else {
                $this->_logger->debug("XML response fail");
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Couldn\'t retrieve payment response')
                );
            }
            return parent::initialize($paymentAction, $stateObject);
    }

    public function hasVerification()
    {
        return true;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_logger->debug("---------barclay direct---------");
        return parent::assignData($data); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Address $billing
         */
            $atts_array = json_decode($payment->getAdditionalInformation('barclay_response_data'), true);
            $order = $payment->getOrder();
            $orderId = isset($atts_array['orderID']) ? $atts_array['orderID'] : "";
            $payId = isset($atts_array['PAYID']) ? $atts_array['PAYID'] : "";
            $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
            $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
            $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";
        if ($ncError == "0") {
            //payment success
            $payment->setCcTransId($payId);
            $payment->setTransactionId($payId);
            $payment->setLastTransId($payId);
            $payment->setIsTransactionClosed(0);
            $payment->setShouldCloseParentTransaction(0);
            $payment->setAdditionalInformation("barclay_transaction_id", $payId);
            $this->_messageManager->addSuccessMessage("Payment success");
            if ($payment->getAdditionalInformation(Constant::HAS3DS) == true) {
                $payment->setIsFraudDetected(!$this->checkShaKeyValid());
            }
        } else {
            $errMsg = 'Something went wrong. Please try again later.';
            if ($ncerrorPlus) {
                $errMsg = $ncerrorPlus;
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __($errMsg)
            );
        }

        return parent::authorize($payment, $amount); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Address $billing
         */
        $order = $payment->getOrder();
        $this->_logger->debug("capture, order: ".$order->getIncrementId());
        $transid = $payment->getAdditionalInformation("barclay_transaction_id");
        if (!!$transid) {
                $payId = $transid;
                $data = [
                    'AMOUNT' => (string)round($amount * 100),
                    'PAYID' => $payId,
                    'OPERATION' => Constant::MAINTENANCE_PARTIAL_CAPTURE
                ];
                $this->barclayDirectHelper->genMaintenanceRequest($data);
                $this->_debug($data);
                $rawXMLResult = $this->barclayDirectHelper->performMaintenance($data);
                $this->_logger->debug("raw response: ". $rawXMLResult);
                if (!!$rawXMLResult) {
                    $xml = new \SimpleXMLElement(trim($rawXMLResult));
                    $atts_object = $xml->attributes();
                    $atts_array = (array)$atts_object;
                    $atts_array = $atts_array['@attributes'];
                    $subpayid = isset($atts_array['PAYIDSUB']) ? $atts_array['PAYIDSUB'] : "";
                    $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
                    $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
                    $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";
                    if ($ncError == '0') {
                        //capture success
                        $payment->setShouldCloseParentTransaction(0);
                        $payment->setTransactionId($payId . "_" . $subpayid)->setIsTransactionClosed(0);
                        $this->_messageManager->addSuccessMessage("Capture success");
                        $this->_messageManager->addWarningMessage("The maintenance orders are always processed offline");
                    } else {
                        $errMsg = 'Something went wrong. Please try again later.';
                        if ($ncerrorPlus) {
                            $errMsg = $ncerrorPlus;
                        }
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __($errMsg)
                        );
                    }
                }

        } else {
            //new order:
            /**
             * @var \Magento\Sales\Model\Order $order
             * @var \Magento\Sales\Model\Order\Address $billing
             */

                $atts_array = json_decode($payment->getAdditionalInformation('barclay_response_data'), true);
                $orderId = isset($atts_array['orderID']) ? $atts_array['orderID'] : "";
                $payId = isset($atts_array['PAYID']) ? $atts_array['PAYID'] : "";
                $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
                $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
                $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";

            if ($ncError == "0") {
                //payment success
                $payment->setCcTransId($payId);
                $payment->setTransactionId($payId);
                $payment->setLastTransId($payId);
                $payment->setIsTransactionClosed(0);
                $payment->setShouldCloseParentTransaction(0);
                $payment->setAdditionalInformation("barclay_transaction_id", $payId);
                $this->_messageManager->addSuccessMessage("Payment success");
                if ($payment->getAdditionalInformation(Constant::HAS3DS) == true) {
                    $payment->setIsFraudDetected(!$this->checkShaKeyValid());
                }
            } else {
                $errMsg = 'Something went wrong. Please try again later.';
                if ($ncerrorPlus) {
                    $errMsg = $ncerrorPlus;
                }
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($errMsg)
                );
            }

        }

        return parent::capture($payment, $amount); // TODO: Change the autogenerated stub
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $this->_logger->debug("refund, order: ".$order->getIncrementId());
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Address $billing
         */

            $transid = $payment->getAdditionalInformation("barclay_transaction_id");
            $payId = $transid;
            $data = [
                'AMOUNT' => (string)round($amount * 100),
                'PAYID' => $payId,
                'OPERATION' => Constant::MAINTENANCE_PARTIAL_REFUND
            ];
            $this->barclayDirectHelper->genMaintenanceRequest($data);
            $this->_debug($data);
            $rawXMLResult = $this->barclayDirectHelper->performMaintenance($data);
            $this->_logger->debug($rawXMLResult);
            if (!!$rawXMLResult) {
                $xml = new \SimpleXMLElement(trim($rawXMLResult));
                $atts_object = $xml->attributes();
                $atts_array = (array)$atts_object;
                $atts_array = $atts_array['@attributes'];
                $subpayid = isset($atts_array['PAYIDSUB']) ? $atts_array['PAYIDSUB'] : "";
                $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
                $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
                $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";
                if ($ncError == '0') {
                    //refund success
                    $payment->setShouldCloseParentTransaction(0);
                    $payment->setTransactionId($payId . '_' . $subpayid . '_' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND)
                        ->setIsTransactionClosed(0);
                    $this->_messageManager->addSuccessMessage("Refund success");
                    $this->_messageManager->addWarningMessage("The maintenance orders are always processed offline");
                } else {
                    $errMsg = 'Something went wrong. Please try again later.';
                    if ($ncerrorPlus) {
                        $errMsg = $ncerrorPlus;
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($errMsg)
                    );
                }
            }


            return parent::refund($payment, $amount); // TODO: Change the autogenerated stub
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Address $billing
         */

            $order = $payment->getOrder();
            $this->_logger->debug("void, order: ".$order->getIncrementId());
            $amount = round($order->getBaseGrandTotal() * 100);
            $transid = $payment->getAdditionalInformation("barclay_transaction_id");
            $data = [
                'PAYID' => $transid,
                'AMOUNT' => (string)$amount,
                'OPERATION' => Constant::MAINTENANCE_DELETE
            ];
            $this->barclayDirectHelper->genMaintenanceRequest($data);
            $this->_debug($data);
            $rawXMLResult = $this->barclayDirectHelper->performMaintenance($data);
            $this->_logger->debug($rawXMLResult);
            if (!!$rawXMLResult) {
                $xml = new \SimpleXMLElement(trim($rawXMLResult));
                $atts_object = $xml->attributes();
                $atts_array = (array)$atts_object;
                $atts_array = $atts_array['@attributes'];
                $ncerrorPlus = isset($atts_array['NCERRORPLUS']) ? $atts_array['NCERRORPLUS'] : "";
                $ncError = isset($atts_array['NCERROR']) ? $atts_array['NCERROR'] : "";
                $status = isset($atts_array['STATUS']) ? $atts_array['STATUS'] : "";
                $payId = isset($atts_array['PAYID']) ? $atts_array['PAYID'] : "";
                if ($ncError == '0') {
                    //cancel success
                    //$order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, 'Payment cancel')->save();
                    $payment->setStatus(self::STATUS_DECLINED);
                    $payment
                        ->setShouldCloseParentTransaction(1)
                        ->setIsTransactionClosed(1);
                } else {
                    $errMsg = 'Something went wrong. Please try again later.';
                    if ($ncerrorPlus) {
                        $errMsg = $ncerrorPlus;
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($errMsg)
                    );
                }
            }

            return parent::void($payment);
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Address $billing
         */
        $this->void($payment);
        return $this;
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

    /**
     * @param array $debugData
     */
    protected function _debug($debugData)
    {
        $this->_logger->debug(
            var_export($this->filterDebugData($debugData, $this->getDebugReplacePrivateDataKeys()), true)
        );
    }

    protected function filterDebugData(array $debugData, array $debugReplacePrivateDataKeys)
    {
        $debugReplacePrivateDataKeys = array_map('strtolower', $debugReplacePrivateDataKeys);

        foreach (array_keys($debugData) as $key) {
            if (in_array(strtolower($key), $debugReplacePrivateDataKeys)) {
                $debugData[$key] = "****";
            } elseif (is_array($debugData[$key])) {
                $debugData[$key] = $this->filterDebugData($debugData[$key], $debugReplacePrivateDataKeys);
            }
        }
        return $debugData;
    }

    public function getDebugFlag()
    {
        return true;
    }

    private function buildFeedbackParam($order_id, $tok)
    {
        return "order_id=".$order_id."&tok=".$tok;
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

    public function canUseInternal()
    {
        return $this->getConfigData('active_moto');
    }

    private function checkShaKeyValid()
    {
        $params = $this->request->getParams();
        $shaSign = isset($params['SHASIGN'])?$params['SHASIGN']:"";
        $shaOutGen = $this->encrypter->generateHashShaOut($params);
        if (strtolower($shaSign) == strtolower($shaOutGen)) {
            return true;
        }
        return false;
    }
}
