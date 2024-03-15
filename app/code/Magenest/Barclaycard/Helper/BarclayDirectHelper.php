<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Helper;

class BarclayDirectHelper
{
    protected $configData;
    protected $logger;
    protected $curl;

    public function __construct(
        \Magenest\Barclaycard\Helper\ConfigData $configData,
        \Magenest\Barclaycard\Helper\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->configData = $configData;
        $this->logger = $logger;
        $this->curl = $curl;
    }

    public function getRandomString($numberChar = 10)
    {
        $rand = substr(hash("sha256", microtime()), rand(0, 26), $numberChar);

        return $rand;
    }

    public function performPayment($data, $check = null)
    {
        ksort($data);
        $Passphrase = $this->configData->getShaInDirect(); // your SHA-IN pass phrase goes here
//        $data_string = "";
        $data_string = [];
        foreach ($data as $key => $value) {
            if($value){
                $data_string[] = $key . '=' . $value . $Passphrase;
            }
//creates the string to hash
        }

        $string_post = [];
        $string_post = implode('', $data_string);
        $SHASign = hash($this->configData->getHashMethod(), $string_post);

        $data['SHASIGN'] = $SHASign;
        $post_string = [];
        foreach ($data as $key => $value) {
            $post_string[] = $key . '=' . $value;
        }

        $http_arr = [];
        foreach ($data as $key => $value) {
            $http_arr[$key] = $value;
        }


        //var_dump($post_string);
        $actual_string = [];
        $actual_string = implode('&', $post_string);
        $string  = http_build_query($http_arr);

        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_HTTPHEADER, ["application/x-www-form-urlencoded"]);
        $this->curl->setOption(CURLOPT_POST, 1);
        $this->curl->setOption(CURLOPT_POSTFIELDS, $string);
        $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curl->get($this->configData->getDirectPayUrl());
        $result = $this->curl->getBody();
        return $result;
    }

    public function genMaintenanceRequest(&$dataReceive)
    {
        $dataReceive['PSPID'] = $this->configData->getPspid();
        $dataReceive['PSWD'] = $this->configData->getPassword();
        $dataReceive['USERID'] = $this->configData->getUserId();
        ksort($dataReceive);
    }

    public function performMaintenance($data)
    {
        $Passphrase = $this->configData->getShaInDirect();
        $data_string = [];
        foreach ($data as $key => $value) {
            $data_string[] = $key . '=' . $value . $Passphrase;
        }
        $string_post = implode('', $data_string);
        $SHASign = hash($this->configData->getHashMethod(), $string_post);

        $data['SHASIGN'] = $SHASign;
        $post_string = [];
        foreach ($data as $key => $value) {
            $post_string[] = $key . '=' . $value;
        }
        foreach ($data as $key => $value) {
            $http_arr[$key] = $value;
        }
        $actual_string = [];
        $actual_string = implode('&', $post_string);
        try {
            $this->curl->post($this->configData->getDirectMaintenanceUrl(), $http_arr);
            $result = $this->curl->getBody();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $result = false;
        }

        return $result;
    }
}
