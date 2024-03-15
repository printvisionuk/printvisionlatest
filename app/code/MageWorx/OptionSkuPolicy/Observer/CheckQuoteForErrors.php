<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionSkuPolicy\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\OptionSkuPolicy\Helper\Data as Helper;
use MageWorx\OptionSkuPolicy\Model\SkuPolicy;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
// use Zend\Console\Response; //depricated
use Magento\Framework\Message\ManagerInterface;

class CheckQuoteForErrors implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SkuPolicy
     */
    protected $skuPolicy;

    /**
     * @param Helper $helper
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param Response $response
     * @param SkuPolicy $skuPolicy
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Helper $helper,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        //Response $response, //depricated
        SkuPolicy $skuPolicy,
        ManagerInterface $messageManager
    ) {
        $this->helper          = $helper;
        $this->responseFactory = $responseFactory;
        $this->url             = $url;
        //$this->response        = $response; //depricated
        $this->skuPolicy       = $skuPolicy;
        $this->messageManager  = $messageManager;
    }

    /**
     * Check quote for errors, if they exist - redirect to cart
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isEnabledSkuPolicy()) {
            return $this;
        }

        $this->skuPolicy->setIsSubmitQuoteFlag(true);

        if ($observer->getQuote()->getHasError()) {
            $redirectUrl   = $this->url->getUrl('checkout');
            $errorMessages = $observer->getQuote()->getMessages() ?: [];
            foreach ($errorMessages as $errorMessage) {
                $this->messageManager->addErrorMessage($errorMessage->getText());
            }
            $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
            //We need to avoid using “exit” in own code for corresponding Marketplace requirements
            //$this->response->send(); //depricated
        }
        return $this;
    }
}