<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Controller\Checkout;

use Magenest\Barclaycard\Controller\Checkout;
use Magenest\Barclaycard\Helper\Constant;

class Direct extends Checkout
{
    /**
     * @var \Magenest\Barclaycard\Helper\Logger $logger ;
     */
    protected $logger;

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'success' => false,
                'error' => true,
                'message' => "Invalid form key"
            ]);
        }
        if ($this->getRequest()->isAjax()) {
            try {
                $order = $this->checkoutSession->getLastRealOrder();
                $payment = $order->getPayment();
                $has3ds = $payment->getAdditionalInformation(Constant::HAS3DS);
                if ($has3ds) {
                    $code = $payment->getAdditionalInformation(Constant::THREEDS_CODE);
                    // phpcs:ignore
                    $decoded = base64_decode($code);
                    return $result->setData([
                        'error' => false,
                        'success' => true,
                        'form' => $decoded,
                        'has3ds' => true
                    ]);
                } else {
                    $this->messageManager->addSuccessMessage("Payment success!");
                    return $result->setData([
                        'error' => false,
                        'success' => true,
                        'has3ds' => false
                    ]);
                }
            } catch (\Exception $e) {
                return $this->jsonFactory->create()->setData([
                    'error' => true,
                    'success' => false,
                    'error_msg' => "pay exception"
                ]);
            }
        }

        return "error";
    }

}
