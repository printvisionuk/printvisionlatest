<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    protected $_config;
    protected $_urlBuilder;

    const CODE = 'barclaycard_direct';

    public function __construct(
        \Magenest\Barclaycard\Helper\ConfigData $config,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
    
        $this->_config = $config;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'genHashUrl' => $this->getUrl('barclaycard/checkout/redirect'),
                    'genDirectUrl' => $this->getUrl('barclaycard/checkout/direct')
                ]
            ]
        ];
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
