<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Model\Quote;

use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;

class ShippingAddressManagement extends \Magento\Quote\Model\ShippingAddressManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var QuoteAddressValidator
     */
    protected $_addressValidator;

    /**
     * @var AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var TotalsCollector
     */
    protected $_totalsCollector;

    /**
     * @var ValidationMessage
     */
    private $_minimumAmountErrorMessage;

    /**
     * Constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param AddressRepositoryInterface $addressRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TotalsCollector $totalsCollector
     * @param ValidationMessage $minimumAmountErrorMessage
     *
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        AddressRepositoryInterface $addressRepository,
        ScopeConfigInterface $scopeConfig,
        TotalsCollector $totalsCollector,
        ValidationMessage $minimumAmountErrorMessage
    ) {
        $this->_quoteRepository           = $quoteRepository;
        $this->_addressValidator          = $addressValidator;
        $this->_logger                    = $logger;
        $this->_addressRepository         = $addressRepository;
        $this->_scopeConfig               = $scopeConfig;
        $this->_totalsCollector           = $totalsCollector;
        $this->_minimumAmountErrorMessage = $minimumAmountErrorMessage;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_quoteRepository->getActive($cartId);

        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('Cart contains virtual product(s) only. Shipping address is not applicable.')
            );
        }

        $saveInAddressBook = $address->getSaveInAddressBook() ? 1 : 0;
        $sameAsBilling     = $address->getSameAsBilling() ? 1 : 0;
        $customerAddressId = $address->getCustomerAddressId();
        $this->_addressValidator->validate($address);
        $quote->setShippingAddress($address);
        $address = $quote->getShippingAddress();

        if ($customerAddressId === null) {
            $address->setCustomerAddressId(null);
        }

        if ($customerAddressId) {
            $addressData = $this->_addressRepository->getById($customerAddressId);
            $address = $quote->getShippingAddress()->importCustomerAddressData($addressData);
        } elseif ($quote->getCustomerId()) {
            $address->setEmail($quote->getCustomerEmail());
        }
        $address->setSameAsBilling($sameAsBilling);
        $address->setSaveInAddressBook($saveInAddressBook);
        $address->setCollectShippingRates(true);

       /* We have comment code because minimum order amount product is not deleted */
 
        try {
            $address->save();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new InputException(__('Unable to save address. Please check input data.'));
        }
        return $quote->getShippingAddress()->getId();
    }

    /**
     * Generate Error Message and Show on Frontend
     *
     * @return Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private function getMinimumAmountErrorMessage()
    {
        if ($this->_minimumAmountErrorMessage === null) {
            return $this->_minimumAmountErrorMessage;
        }
        return $this->_minimumAmountErrorMessage;
    }
}
