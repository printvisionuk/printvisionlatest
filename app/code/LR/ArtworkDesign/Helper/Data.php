<?php
namespace LR\ArtworkDesign\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Convert\ConvertArray
     */
    protected $convertArray;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

     /**
      * @var \Magento\Directory\Model\CountryFactory
      */
    protected $countryFactory;

    /**
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Convert\ConvertArray $convertArray
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */

    public function __construct(
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Convert\ConvertArray $convertArray,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Directory\Model\CountryFactory  $countryFactory
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->convertArray = $convertArray;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->productRepository = $productRepository;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Generate order xml file
     * @return bool
     */
    public function generateOrderItemXmlFile($order)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GenerateOrderItemXml22.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        try {

            foreach ($order->getAllVisibleItems() as $_item) {
                // $product = $this->productRepository->get($_item->getSku());
                $data = [
                    "Name" => $order->getCustomerName(),
                    "Company" => $order->getShippingAddress()->getCompany(),
                    "Telephone" => $order->getShippingAddress()->getTelephone(),
                    "Email" => $order->getCustomerEmail(),
                    "testemailaddress" => 'dharmesh@printvision.co.uk',
                    "Address" => $this->getAddress($order),
                    "Document" => $this->getDocument($order,$_item->getId())
                ];

                foreach($data as $key => $value)  {
                    if(empty($value)) {
                        unset($data[$key]);
                    }
                }

                // ConvertArray function assocToXml to create xmlContents
                $xmlContents = $this->convertArray->assocToXml(["Contact" => $data], "Clarity");
                $xmlContents->addAttribute('xmlns', 'http://www.touchsystems.co.uk/schemas');
                $xmlContents->addAttribute('Type', 'Order');
                $xmlContents->addAttribute('DateTime', '2011-04-04T17:58:41.000000');
                $xmlContents->addAttribute('Source', 'Customer Website');

                // convert it to xml using asXML() function
                $content = $xmlContents->asXML();
                $this->file->write($this->createOrderItemFile($order, $_item->getId()), $content);
                $this->copyArtworkFiles($order, $_item->getId());
            }
        } catch (\Exception $e) {
            $logger->info('Generate Order Item Xml Error:: '.print_r($e->getMessage(), true));
        }
    }

    /**
     * Get Order Address
     *
     * @return aaray
     */
    public function getAddress($order)
    {
        $countryCode = $order->getBillingAddress()->getCountryId();
        $billingstreet = $order->getBillingAddress()->getStreet();

        $address = [];
        $address['Address1'] = $billingstreet[0];
        if(!empty($address['City'])) {
            $address['City'] = $order->getBillingAddress()->getCity();
        }
        if(!empty($address['County'])) {
            $address['County'] = $order->getBillingAddress()->getRegion();
        }
        $address['Postcode'] = $order->getBillingAddress()->getPostCode();
        $address["Country"] =  $this->getCountryname($countryCode);
        return $address;
    }

    /**
     * Get Document Data
     *
     * @return aaray
     */
    public function getDocument($order, $itemId)
    {
        $shippingstreet = $order->getShippingAddress()->getStreet();

        $document = [];
        $document['Title'] = $order->getIncrementId();
        if(!empty($order->getStatusHistoryCollection()->getFirstItem()->getComment())) {
            $document['Notes'] = $order->getStatusHistoryCollection()->getFirstItem()->getComment();
        }
        $document['Detail']['CustOrderNo'] = $order->getIncrementId();
        $document['Detail']['RequiredDate'] = $order->getCreatedAt();
        foreach ($order->getAllVisibleItems() as $item) {
            if($itemId == $item->getId()){
                $product = $this->productRepository->get($item->getSku());
                $document['Item']['PartCode'] = $item->getSku();
                $document['Item']['Description'] = $item->getName();
                $document['Item']['Quantity'] = $item->getQtyOrdered();
                $document['Item']['UnitPrice'] = number_format($product->getPrice(), 2);
            }
        }
        $document['DeliveryAddress']['Contact'] = $order->getCustomerName();
        $document['DeliveryAddress']['Address1'] = $shippingstreet[0];
        if(!empty($shippingstreet[1])) {
            $document['DeliveryAddress']['Address2'] = $shippingstreet[1];
        }
        if(!empty($document['DeliveryAddress']['City'])) {
            $document['DeliveryAddress']['City'] = $order->getShippingAddress()->getCity();
        }
        if(!empty($document['DeliveryAddress']['County'])) {
            $document['DeliveryAddress']['County'] = $order->getShippingAddress()->getRegion();
        }
        $document['DeliveryAddress']['Postcode'] = $order->getShippingAddress()->getPostCode();
        $document['DeliveryAddress']['Telephone'] = $order->getShippingAddress()->getTelephone();
        return $document;
    }

    /**
     * Get Country name
     *
     * @return name
     */
    public function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * Create artwork order file
     *
     * @return string
     */
    public function createOrderItemFile($order, $itemId)
    {
        $fileDirPath = $this->createFileDirPath($order, $itemId);

        if (!is_dir($fileDirPath)) {
            mkdir($fileDirPath, 0777, true);
        }
        $fileName = $itemId.'.xml';
        $finalPath = $fileDirPath.$fileName;
        return $finalPath;
    }

    /**
     * Create artwork directory path
     *
     * @return string
     */
    public function createFileDirPath($order, $itemId)
    {
        $orderEntityID = $order->getId();
        $orderIncrementID = $order->getIncrementId();
        $mediaPath = $this->directoryList->getPath('media');
        $fileDirPath = $mediaPath.'/lr/artwork/'.$orderEntityID.'-'.$orderIncrementID.'/'.$itemId.'/';

        return $fileDirPath;
    }

    /**
     * Get Ordered item custom option values
     * 
     * @return array
     */
    public function getItemOptionValues($item)
    {
        $itemOptionValues = array();
        $options = $item->getProductOptions();
        if (isset($options['options']) && !empty($options['options'])) {
            foreach ($options['options'] as $option) {
                if($option['option_type'] == "file" && $option['label'] == "Artwork"){
                    $optionValue = $this->json->unserialize($option['option_value']);
                    $itemOptionValues['title']= $optionValue['title'];
                    $itemOptionValues['fullpath']= $optionValue['fullpath'];
                }
            }
        }
        return $itemOptionValues;
    }
    

    /**
     * Check has artwork option in ordered items
     * 
     * @return boolean
     */
    public function copyArtworkFiles($order, $itemId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GenerateOrderItemXml22.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $orderEntityID = $order->getId();
        $orderIncrementID = $order->getIncrementId();
        $mediaPath = $this->directoryList->getPath('media');
        $artworkDir = $mediaPath.'/lr/artwork/'.$orderEntityID.'-'.$orderIncrementID.'/';
        foreach ($order->getAllVisibleItems() as $item) {
            $itemOptionValues = $this->getItemOptionValues($item);
            $itemId = $item->getItemId();
            if(!empty($itemOptionValues)){
                $sourceFile = $itemOptionValues['fullpath'];
                $destinationPath = $artworkDir.$itemId.'/'.$itemOptionValues['title'];
                try{
                    $this->file->cp($sourceFile, $destinationPath);
                    $logger->info('Artwork file are copied to a custom path.');
                } catch(\Exception $e) {
                    $logger->info('Artwork file copy time Error:: '.print_r($e->getMessage(), true));
                }
            }
        }
    }
}
