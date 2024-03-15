<?php
namespace SamSolutions\Artwork\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    const XML_PATH_NAME_SENDER = 'trans_email/ident_general/name';
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_sales/email';

    protected $transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $escaper;
    protected $customerSession;
    
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper,
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function sendOrdeArtworkUploadNotify($incrementId,$productData)
    {        
        try 
        {
            $this->inlineTranslation->suspend();
            $error = false;
            $customer = $this->customerSession->getCustomer();
            $customerName = $customer->getFirstname()." ".$customer->getLastname();

            $senderName = $this->scopeConfig->getValue(self::XML_PATH_NAME_SENDER,ScopeInterface::SCOPE_STORE);
            $senderEmail = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER,ScopeInterface::SCOPE_STORE);
            $sender = [
                'name' => $senderName,
                'email' => "sales@printvision.co.uk"
            ];
            $receiverEmail = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT,ScopeInterface::SCOPE_STORE);
            
            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
            $templateVars = array(
                                'store' => $this->storeManager->getStore(),
                                'customer_name' => $customerName,
                                'order_id' => $incrementId,
                                'product'  => $productData
                            );
            $this->inlineTranslation->suspend();

            
            $transport = $this->transportBuilder->setTemplateIdentifier('order_artwork_upload_notification_template')
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($sender)
                            ->addTo($receiverEmail)
                            //->addCc('vipul@logicrays.com')
                            ->addCc('dharmesh@printvision.co.uk')
                            ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

        } 
        catch (\Exception $e) 
        {
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug($e->getMessage());
        }

    }
}