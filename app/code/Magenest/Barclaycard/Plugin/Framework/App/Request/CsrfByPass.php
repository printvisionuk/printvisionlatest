<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Plugin\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class CsrfByPass
{
    const BY_PASS_URI = 'barclaycard/checkout';

    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ){
        if (strpos($request->getPathInfo(), self::BY_PASS_URI) !== false) {
            return true;
        } else {
            return $proceed($request, $action);
        }
    }
}