<?php
namespace Designnbuy\CustomOptionPlugin\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class DynamicPricing implements ObserverInterface
{
    const ATTR_DISCOUNT_FREQUENCY_CODE = 'square_area_pricing'; //attribute code

    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $post = $this->request->getPost();
        $post = $post['product'];
        $frequencyData = isset($post[self::ATTR_DISCOUNT_FREQUENCY_CODE]) ? $post[self::ATTR_DISCOUNT_FREQUENCY_CODE] : '';
        
        $product->setSquareAreaPricing($frequencyData);
        $requiredParams = ['square_area', 'price','group_id']; // PARAMS you defined in Frequency.php file //by Vips
        if (is_array($frequencyData)) {
            //$frequencyData = $this->removeEmptyArray($frequencyData, $requiredParams);
            $product->setSquareAreaPricing(json_encode($frequencyData));
        }
    }

    private function removeEmptyArray($discountData, $requiredParams) {
        $requiredParams = array_combine($requiredParams, $requiredParams);
        $reqCount = count($requiredParams);

        foreach ($discountData as $key => $values) {
            $values = array_filter($values);
            $inersectCount = count(array_intersect_key($values, $requiredParams));
            if ($reqCount != $inersectCount) {
                unset($discountData[$key]);
            }  
        }
        return $discountData;
    }
}