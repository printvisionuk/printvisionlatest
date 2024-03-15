<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Block\Adminhtml\Config;

class HppConfig extends WebConfig
{
    /**
     * @var string
     */
    protected $code = 'magenest_barclaycard';

    protected function webConfig()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $notificationUrl = $baseUrl."barclaycard/checkout/response";
        $notificationErrUrl = $baseUrl."barclaycard/checkout/responseNok";
        $html = "<h2>Please copy and paste these url to Barclaycard Configuration</h2>";
        $html .= "<div class='input-url'>";
        $html .= "<div><label for='main_url'>Main Url <input size='100' id='main_url' type='text' readonly value='$baseUrl'></label></div>";
        $html .= "<div><label for='notification_url'>Notification Url <input size='100' id='notification_url' type='text' readonly value='$notificationUrl'></label></div>";
        $html .= "<div><label for='notification_err_url'>Notification Error Url <input size='100' id='notification_err_url' type='text' readonly value='$notificationErrUrl'></label></div>";
        $html .= "</div>";
        return $html;
    }
}
