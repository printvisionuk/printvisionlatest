<?php
namespace Prince\Productattach\Controller\Index;

//use Magento\Framework\App\Action\Context;
//use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Tierprice extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory,
        \Magento\Catalog\Helper\Data $taxHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_productRepository = $productRepository;
        $this->session = $customerSession;
        $this->priceFactory = $priceFactory;
        $this->taxHelper = $taxHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $requestedSku = $this->getRequest()->getParam("sku");
        $requestedqty = $this->getRequest()->getParam("qty");
        $selectedValues = $this->getRequest()->getParam("selectedvalues");

        $product = $this->_productRepository->get($requestedSku);
        $productType = $product->getTypeID();
        $simpleproduct = "";
        if ($productType == "simple") {
            $simpleproduct = $product;
        } elseif ($selectedValues && $productType == "configurable") {
            parse_str($selectedValues, $attributeOptions);
            if ($attributeOptions) {
                if (
                    isset($attributeOptions["super_attribute"]) &&
                    !empty($attributeOptions["super_attribute"])
                ) {
                    $attributesInfo = $attributeOptions["super_attribute"];
                    $simpleproduct = $product
                        ->getTypeInstance(true)
                        ->getProductByAttributes($attributesInfo, $product);
                }
            }
        }
        $customerGroupId = 0;
        if ($this->session->isLoggedIn()) {
            $customerGroupId = $this->_customerSession
                ->getCustomer()
                ->getGroupId();
        }

        $resultarray["price"] = (float)($simpleproduct->getFinalPrice() * $requestedqty);
        $resultarray["incl_price"] = (float)($this->taxHelper->getTaxPrice(
            $simpleproduct,
            $simpleproduct->getFinalPrice(),
            true
        ) * $requestedqty);
        if ($simpleproduct->getTierPrice()) {
            foreach ($simpleproduct->getTierPrice() as $price) {
                if ($requestedqty >= $price["price_qty"]) {
                    $resultarray["price"] = (float)($price["price"] * $requestedqty);
                    $resultarray["incl_price"] = (float)($this->taxHelper->getTaxPrice(
                        $simpleproduct,
                        $price["price"],
                        true
                    ) * $requestedqty);
                }
            }
        }
        $resultarray["status"] = "success";

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultarray);
        return $resultJson;
    }
}
