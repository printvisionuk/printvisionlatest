<?php
namespace LR\CustomOptionPricing\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \LR\CustomOptionPricing\Model\OptionsTierPriceFactory;


class OptionValuesTierPrice implements ObserverInterface
{
    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;
    protected $productFactory;
    protected $otpFactory;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        OptionsTierPriceFactory $otpFactory
    )
    {
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->otpFactory = $otpFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getProduct();
        $post = $this->request->getPost();
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/savedTierPriceData.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');

        $savedProductOptions = $this->productFactory->create()->load($product->getId())->getOptions();
        $savedTierPriceData = [];


      /*  foreach($savedProductOptions->getOptions() as $productOptions)
        {
            var_dump($productOptions->getValues());
            echo "sss ";
        }

         die("Dfsdf");*/
        

        $logger->info("========================================");

        foreach($savedProductOptions as $option)
        {
           
            if($option['is_custom_pricing'] == 1 && $option->getValues() != '')
            {
                foreach($option->getValues() as $values)
                {
                    if($values->getValuesTierPrice())
                    {
                        $savedTierPriceData[$values->getOptionId()][$values->getOptionTypeId()] = json_decode($values->getValuesTierPrice(),true);
                    }
                }
            }
        }

        $logger->info(print_r($savedTierPriceData, true));
       // die("SDfsdf ");

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/tierPriceArray.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');

        $tierPriceArray = [];
        if(isset($post['product']['options']))
        {
            foreach($post['product']['options'] as $productOption)
            {
                if($productOption['is_custom_pricing'] == 1 && $productOption['values'])
                {
                    foreach($productOption['values'] as $optionValue)
                    {
                        if($optionValue['values_tier_price'])
                        {
                            $optionId = $productOption['option_id'];
                            $logger->info("info = ". $optionId); 

                            if(isset($savedTierPriceData[$optionId]))
                            {
                                $logger->info(print_r($savedTierPriceData[$optionId], true));
                            }
                            else
                            {
                                $tierPriceArray[$optionId][] = json_decode($optionValue['values_tier_price']);
                                $optionTypeIdValue = $optionValue['option_type_id'];

                                $addTierPriceDataArray = json_decode($optionValue['values_tier_price']);
                                foreach($addTierPriceDataArray as $tierPriceObj)
                                {
                                    $tierPriceData = json_decode(json_encode($tierPriceObj), true);
                                    $priceObject = $this->otpFactory->create();
                                    $logger->info(print_r($tierPriceData, true));
                                    $priceObject->setData($tierPriceData);
                                    $priceObject->setOptionTypeId($optionTypeIdValue)->save();
                                }
                                
                            }
                            
                        }
                    }
                }
            }
        }

        
        
       // $logger->info(print_r($post['product']['options'], true)); 
        $logger->info("========================================");
        // $logger->info(print_r($tierPriceArray, true));

    }




    private function removeEmptyArray($discountData, $requiredParams) {
        /*$requiredParams = array_combine($requiredParams, $requiredParams);
        $reqCount = count($requiredParams);

        foreach ($discountData as $key => $values) {
            $values = array_filter($values);
            $inersectCount = count(array_intersect_key($values, $requiredParams));
            if ($reqCount != $inersectCount) {
                unset($discountData[$key]);
            }  
        }
        return $discountData;*/
    }
}