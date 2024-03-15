<?php
namespace LR\AdminNotify\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class NotifyMailToAdmin implements ObserverInterface
{

    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';
    const XML_PATH_NAME_SENDER = 'trans_email/ident_general/name';
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_sales/email';


    protected $_transportBuilder;
    protected $inlineTranslation;
    protected $scopeConfig;
    protected $storeManager;
    protected $_escaper;
    protected $_request;

    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_request = $request;
        $this->_escaper = $escaper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getData('customer');

        $postData = $this->_request->getPost();

        $this->inlineTranslation->suspend();
        try 
        {
            $error = false;

            $senderName = $this->scopeConfig->getValue(self::XML_PATH_NAME_SENDER,ScopeInterface::SCOPE_STORE);
            $senderEmail = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER,ScopeInterface::SCOPE_STORE);
            $sender = [
                'name' => $senderName,
                'email' => "lee@printvision.co.uk"
            ];
            $receiverEmail = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT,ScopeInterface::SCOPE_STORE);
            $userType = $postData['user_type'];
            $websiteUrl = $postData['website'];
            $company = $postData['company'];
            $telephone = $postData['telephone'];

            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
            $templateVars = array(
                                'store' => $this->storeManager->getStore(),
                                'firstname' => $customer->getFirstname(),
                                'lastname'    => $customer->getLastname(),
                                'email'     => $customer->getEmail(),
                                'user_type' => $userType,
                                'company'   => $company,
                                'website'   => $websiteUrl,
                                'telephone' => $telephone
                            );
            $this->inlineTranslation->suspend();

            
            $transport = $this->_transportBuilder->setTemplateIdentifier('customer_register_admin_notify')
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars(['customer' => $templateVars])
                            ->setFrom($sender)
                            ->addTo($receiverEmail)
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