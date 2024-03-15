<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Controller\Checkout;

use Magenest\Barclaycard\Controller\Checkout;

class Redirect extends Checkout
{
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
            $this->barclayLogger->debug("Redirecting user to url: ".$this->barclayConfig->getPayUrl());
            $order = $this->checkoutSession->getLastRealOrder();
            $payment = $order->getPayment();
            $formInfo = $payment->getAdditionalInformation("barclay_form_param");
            $formInfo = json_decode($formInfo, true);
            return $result->setData([
                'payUrl' => $this->barclayConfig->getPayUrl(),
                'data' => $formInfo,
                'error' => false,
                'success' => true,
            ]);
        }

        return "error";
    }
}
