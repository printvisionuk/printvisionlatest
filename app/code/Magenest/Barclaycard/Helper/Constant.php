<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Helper;

class Constant
{
    const RESPONSE_STATUS_ACCEPT = "ACCEPT";
    const RESPONSE_STATUS_CANCEL = "CANCEL";
    const RESPONSE_STATUS_DECLINE = "DECLINE";
    const RESPONSE_STATUS_EXCEPTION = "EXCEPTION";
    const RESPONSE_STATUS_ERROR = "ERROR";

    const OPERATION_AUTHORIZE = "RES";
    const OPERATION_AUTHORIZE_CAPTURE = "SAL";
    const PAYMENT_OPERATION = [
        'authorize' => "RES",
        'authorize_capture' => "SAL"
    ];

    const MAINTENANCE_PARTIAL_CAPTURE = "SAL";
    const MAINTENANCE_CAPTURE = "SAS";
    const MAINTENANCE_PARTIAL_REFUND = "RFD";
    const MAINTENANCE_REFUND = "RFS";
    const MAINTENANCE_DELETE = "DES";
    const MAINTENANCE_VOID = "DEL";
    const MAINTENANCE_RENEW = "REN";


    const THREEDS_CODE = "threeds";
    const HAS3DS = "has3ds";
    const CHECK_KEY = "checkkey";

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
