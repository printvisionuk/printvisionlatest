<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Helper;

class Encrypter
{
    public function __construct(\Magenest\Barclaycard\Helper\ConfigData $barclayConfig)
    {
        $this->barclayConfig = $barclayConfig;
        $this->shaAlgorithm = $this->barclayConfig->getHashMethod();
    }

    private $shaOutParams = "AAVADDRESS,AAVCHECK,AAVMAIL,AAVNAME,AAVPHONE,AAVZIP,ACCEPTANCE,ALIAS,AMOUNT,BIC,BIN,BRAND,CARDNO,CCCTY,CN,COLLECTOR_BIC,COLLECTOR_IBAN,COMPLUS,CREATION_STATUS,CREDITDEBIT,CURRENCY,CVCCHECK,DCC_COMMPERCENTAGE,DCC_CONVAMOUNT,DCC_CONVCCY,DCC_EXCHRATE,DCC_EXCHRATESOURCE,DCC_EXCHRATETS,DCC_INDICATOR,DCC_MARGINPERCENTAGE,DCC_VALIDHOURS,DEVICEID,DIGESTCARDNO,ECI,ED,EMAIL,ENCCARDNO,FXAMOUNT,FXCURRENCY,IP,IPCTY,MANDATEID,MOBILEMODE,NBREMAILUSAGE,NBRIPUSAGE,NBRIPUSAGE_ALLTX,NBRUSAGE,NCERROR,ORDERID,PAYID,PAYMENT_REFERENCE,PM,SCO_CATEGORY,SCORING,SEQUENCETYPE,SIGNDATE,STATUS,SUBBRAND,SUBSCRIPTION_ID,TRXDATE,VC";
//    private $shaOutParams =["AAVADDRESS","AAVCHECK","AAVMAIL","AAVNAME","AAVPHONE","AAVZIP","ACCEPTANCE","ALIAS","AMOUNT","BIC","BIN","BRAND","CARDNO","CCCTY","CN","COLLECTOR_BIC","COLLECTOR_IBAN","COMPLUS","CREATION_STATUS","CREDITDEBIT","CURRENCY","CVCCHECK","DCC_COMMPERCENTAGE","DCC_CONVAMOUNT","DCC_CONVCCY","DCC_EXCHRATE","DCC_EXCHRATESOURCE","DCC_EXCHRATETS","DCC_INDICATOR","DCC_MARGINPERCENTAGE","DCC_VALIDHOURS","DEVICEID","DIGESTCARDNO","ECI","ED","EMAIL","ENCCARDNO","FXAMOUNT","FXCURRENCY","IP","IPCTY","MANDATEID","MOBILEMODE","NBREMAILUSAGE","NBRIPUSAGE","NBRIPUSAGE_ALLTX","NBRUSAGE","NCERROR","ORDERID","PAYID","PAYMENT_REFERENCE","PM","SCO_CATEGORY","SCORING","SEQUENCETYPE","SIGNDATE","STATUS","SUBBRAND","SUBSCRIPTION_ID","TRXDATE","VC"];
    private $shaOutArr;
    private $shaAlgorithm;

    public function generateHashShaOut($mFormParams)
    {
        $mPassword = $this->barclayConfig->getShaOut();
        $this->shaOutArr = explode(",", $this->shaOutParams);
//        $this->shaOutArr = $this->shaOutParams;
        $arrUpper = array_change_key_case($mFormParams, CASE_UPPER);
        ksort($arrUpper);
        $out = [];
        foreach ($arrUpper as $key => $param) {
            if (in_array(strtoupper($key), $this->shaOutArr) && (trim($param) != "")) {
                $out[] = strtoupper($key) . "=" . $param;
            }
        }

        $out = implode($mPassword, $out) . $mPassword;
        $shaResult = strtoupper(hash($this->shaAlgorithm, $out));

        return $shaResult;
    }

    public function generateHash(&$mFormParams)
    {
        $mPassword = $this->barclayConfig->getShaIn();
        ksort($mFormParams);

        $out = [];
        foreach ($mFormParams as $key => $param) {
            $out[] = strtoupper($key) . "=" . $param;
        }

        $out = implode($mPassword, $out) . $mPassword;

        //$mFormParams['SHASIGN'] = strtoupper(sha1($out));
        $mFormParams['SHASIGN'] = strtoupper(hash($this->shaAlgorithm, $out));
    }
}
