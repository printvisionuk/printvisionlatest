<?php
namespace LR\CustomOptionPricing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{

    protected $productFactory;
    protected $optionCollection;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionCollection,
        Context $context
    ) {
        $this->productFactory = $productFactory;
        $this->optionCollection = $optionCollection;
        parent::__construct($context);

    }

    public function getProductById($id)
    {
        return $this->productFactory->create()->load($id);
    }


    public function getGroupBaseOptionValuesSquareAreaPricing($groupId,$id)
    {
        $product = $this->getProductById($id);
        try {
              $productOption = $this->optionCollection->create()->getProductOptions($product->getEntityId(),$product->getStoreId(),false);
                $optionData = [];
            foreach($productOption as $option) {
                if($option->getIsCustomPricing() == 1)
                {
                    $optionId = $option->getId();
                    $optionValues = $product->getOptionById($optionId);

                    if ($optionValues->getValues() != '') {
                        foreach($optionValues->getValues() as $values) {
                            $valuesTierPriceString =  $values->getValuesTierPrice();
                            $priceDataDecode = json_decode($valuesTierPriceString,true);
                            
                            $sortedPriceDataDecode = [];
                            foreach ($priceDataDecode as $key => $sapObj) {
                                if(isset($sapObj['group_id']) && $sapObj['group_id'] == $groupId)   {
                                    // unset($priceDataDecode[$key]);
                                    $sortedPriceDataDecode[$sapObj['square_area']] = $sapObj;
                                }
                            }

                            if(!empty($sortedPriceDataDecode)){
                                ksort($sortedPriceDataDecode);
                                $optionData[$values->getOptionTypeId()] = array_values($sortedPriceDataDecode);
                            }
                        }
                    }                   
                }
            }
        } catch (\Exception $exception) {
            //throw new \Magento\Framework\Exception\NoSuchEntityException(__('Such product doesn\'t exist'));
        }

        return json_encode($optionData);
    }

}
