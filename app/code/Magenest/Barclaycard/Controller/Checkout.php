<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */


namespace Magenest\Barclaycard\Controller;

use Magento\Framework\Exception\LocalizedException;

abstract class Checkout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $resultJsonFactory;
    protected $orderFactory;
    /** @var \Magenest\Barclaycard\Model\BarclaycardPayment $barclaycardPayment */
    protected $barclaycardPayment;
    protected $barclayDirect;
    protected $jsonFactory;
    protected $barclayConfig;
    protected $barclayLogger;
    protected $formKeyValidator;
    protected $orderSender;
    protected $chargeFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magenest\Barclaycard\Helper\ConfigData $barclayConfig,
        \Magenest\Barclaycard\Model\BarclayDirect $barclayDirect,
        \Magenest\Barclaycard\Helper\Logger $barclayLogger,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\Barclaycard\Model\ChargeFactory $chargeFactory,
        $params = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->barclayDirect = $barclayDirect;
        $this->barclaycardPayment = $paymentHelper->getMethodInstance('magenest_barclaycard');
        $this->barclayConfig = $barclayConfig;
        $this->jsonFactory = $resultJsonFactory;
        $this->barclayLogger = $barclayLogger;
        $this->formKeyValidator = $formKeyValidator;
        $this->chargeFactory = $chargeFactory;
        parent::__construct($context);
    }

    protected function processOrder(){
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $orderId = $this->getRequest()->getParam('order_id');
        $this->barclayLogger->debug("Orderid ". $orderId);
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        $payment = $order->getPayment();
        $token = $this->getRequest()->getParam('tok');
        $originToken = $payment->getAdditionalInformation("barclay_token");
        if($token != $originToken){
            throw new LocalizedException(__("Invalid response"));
        }
        $params = $this->getRequest()->getParams();
        $payid = $this->getRequest()->getParam('PAYID');
        $paymentMethod = $this->getRequest()->getParam('PM');
        $cardNo = $this->getRequest()->getParam('CARDNO');
        $status = $this->getRequest()->getParam('STATUS');

        //pay appect
        $order->setCanSendNewEmailFlag(true);
        $payment->setAdditionalInformation("barclay_response_data", json_encode($params));
        $payment->setTransactionId($payid);
        $payment->setLastTransId($payid);
        $payment->setIsTransactionClosed(false);
        $payment->setAdditionalInformation('response_code', $status);
        $payment->setAdditionalInformation('transaction_id', $payid);
        $payment->setAdditionalInformation('payment_method', $paymentMethod);
        $payment->setAdditionalInformation('cardNo', $cardNo);
        $payment->save();
        $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();
        $paymentAction = $payment->getAdditionalInformation("barclay_pay_action");

        switch ($status) {
            case 5:
                $order->addStatusHistoryComment("Authorised");
                break;
            case 9:
                $order->addStatusHistoryComment("Payment requested");
                break;
            case 51:
                $order->addStatusHistoryComment("Authorisation waiting");
                break;
            case 91:
                $order->addStatusHistoryComment("Payment processing");
                break;
            case 92:
                $order->addStatusHistoryComment("Payment uncertain");
                break;
            default:
                $order->addStatusHistoryComment("Payment completed, status unidentified");
                break;
        }
        if ($paymentAction == 'authorize_capture') {
            $payment->setAmountAuthorized($totalDue);
            $payment->setBaseAmountAuthorized($baseTotalDue);
            $payment->capture(null);
        } else {
            $payment->authorize(true, $baseTotalDue);
            $payment->setAmountAuthorized($totalDue);
        }
        $payment->setAdditionalInformation("barclay_payment_success", true);
        $order->save();

        if ($order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (\Exception $e) {
                $this->barclayLogger->critical($e->getMessage());
            }
        }
    }
}
