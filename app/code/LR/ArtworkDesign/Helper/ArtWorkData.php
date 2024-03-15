<?php
namespace LR\ArtworkDesign\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SamSolutions\Artwork\Model\Email;

class ArtWorkData extends AbstractHelper
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
     * @var \SamSolutions\Artwork\Model\Email
     */
    private $emailModel;    

    /**
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Convert\ConvertArray $convertArray
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */

    public function __construct(
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Convert\ConvertArray $convertArray,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $json,
        Email $emailModel
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->convertArray = $convertArray;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->emailModel = $emailModel;
    }

    /**
     * GetArtWorkData function
     *
     * @param [type] $order
     * @return mixed
     */
    public function getArtWorkData($order)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GenerateOrderXml22.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
    
        $hasArtworkOrder = $this->isArtworkOrder($order);
        if ($hasArtworkOrder) {
            $logger->info('#############################################');
            $logger->info('START :: Artwork process for order #'.$order->getIncrementId());
            try {
                $this->generateOrderXml($order);
                $logger->info('An order XML file are generated.');
                $logger->info('END :: Artwork process');
            } catch (\Exception $e) {
                $logger->info('Generate Order Xml Error:: '.print_r($e->getMessage(), true));
            }
        }
    }

    /**
     * GenerateOrderXml function
     *
     * @param [type] $order
     * @return mixed
     */
    public function generateOrderXml($order)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GenerateOrderXml22.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        try {
            $products = [
                "customer_name" => $order->getCustomerName(),
                "customer_email" => $order->getCustomerEmail(),
                "job_number" => $order->getIncrementId(),
                "items" => $this->getOrderItems($order)
            ];

            // ConvertArray function assocToXml to create xmlContents

            $xmlContents = $this->convertArray->assocToXml(["magento" => $products], "Clarity");
            $xmlContents->addAttribute('xmlns', 'http://www.touchsystems.co.uk/schemas');
            $xmlContents->addAttribute('Type', 'Order');
            $xmlContents->addAttribute('DateTime', '2011-04-04T17:58:41.000000');
            $xmlContents->addAttribute('Source', 'Customer Website');

            // convert it to xml using asXML() function
            $content = $xmlContents->asXML();
            $this->file->write($this->createArtWorkOrderFile($order), $content);
        } catch(\Exception $e) {
            $logger->info('Generate Order Xml Error1:: '.print_r($e->getMessage(), true));
        }
    }
 
    /**
     * Get Ordered items collection
     * 
     * @return array
     */
     public function getOrderItems($order)
     {
        $count = 1;
        $items = array();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $artwordDirPath = $mediaUrl.'lr/artwork/'.$order->getId().'-'.$order->getIncrementId().'/';

        foreach ($order->getAllVisibleItems() as $item) {
            $itemOptionValues = $this->getItemOptionValues($item);
            $itemAttributeInfo = $this->getItemAttributeInfo($item);
            $itemId = $item->getItemId();
            $items['item_'.$count]['product_sku'] = $item->getSku();
            if(!empty($itemAttributeInfo)){
                $items['item_'.$count]['product_spec'] = $itemAttributeInfo['value'];
            }
            if(!empty($itemOptionValues)){
                $items['item_'.$count]['artwork'] = $artwordDirPath.$itemId.'/'.$itemOptionValues['title'];
            }
            $count++;
        }

        return $items;
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
     * Get Ordered item attribute information values
     * 
     * @return array
     */
    public function getItemAttributeInfo($item)
    {
        $itemAttributeValues = array();
        $options = $item->getProductOptions();
        if (isset($options['options']) && !empty($options['options'])) {
            if (isset($options['attributes_info'])) {
                foreach ($options['attributes_info'] as $attribute) {
                    if($attribute['label'] == "Printed Sides"){
                        $itemAttributeValues['value'] = $attribute['value'];
                    }
                }
            }
        }
        return $itemAttributeValues;
    }
    /**
     * Create artwork order file
     * 
     * @return string
     */
    public function createArtWorkOrderFile($order)
    {
        $orderIncrementID = $order->getIncrementId();
        $artworkDirPath = $this->createArtWorkDirPath($order);

        if(!is_dir($artworkDirPath))
        {
           mkdir($artworkDirPath, 0777, true);
        }

        $fileName = $orderIncrementID.'.xml';
        $finalPath = $artworkDirPath.$fileName;

        return $finalPath;
    }

    /**
     * Create artwork directory path
     * 
     * @return string
     */
    public function createArtWorkDirPath($order)
    {
        $orderEntityID = $order->getId();
        $orderIncrementID = $order->getIncrementId();
        $mediaPath = $this->directoryList->getPath('media');
        $artworkDirPath = $mediaPath.'/lr/clarity/';

        return $artworkDirPath;
    }

    /**
     * Check has artwork option in ordered items
     * 
     * @return boolean
     */
    public function isArtworkOrder($order)
    {
        $hasArtwork = false;
        foreach ($order->getAllVisibleItems() as $item) {
            $itemOptions = $item->getData('product_options');
            if($itemOptions != null){
                if(isset($itemOptions['options'])){
                    foreach($itemOptions['options'] as $option){
                        if($option['label'] == 'Artwork'){
                            $hasArtwork = true;
                            continue;
                        }
                    }
                }
            }
        }
        return $hasArtwork;
    }

    public function sendArtworkMissingMail($order)
    {    
        try {
            $items = $order->getItems();
            foreach ($items as $item) {
                 
                if (isset($item->getProductOptions()['additional_options'])) {
                    
                    foreach ($item->getProductOptions()['additional_options'] as $option) {
                        
                        if ($option['value'] == 'Yes' || $option['value'] == '1') {
                            
                            $this->emailModel->send($order);
                            
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }

        } catch(\Exception $e) {
            $e->getMessage();
        }

    }
}
