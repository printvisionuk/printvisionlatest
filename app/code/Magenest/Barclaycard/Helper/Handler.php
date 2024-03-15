<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Helper;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $fileName = '/var/log/barclaycard/debug.log';
    protected $loggerType = \Monolog\Logger::DEBUG;
}
