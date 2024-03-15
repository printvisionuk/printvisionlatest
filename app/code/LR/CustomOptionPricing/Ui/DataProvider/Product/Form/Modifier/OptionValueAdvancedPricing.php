<?php
namespace LR\CustomOptionPricing\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use \Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Modal;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use LR\CustomOptionPricing\Ui\DataProvider\Product\Form\Modifier\ModifierInterface;
use MageWorx\OptionAdvancedPricing\Helper\Data as Helper;
use MageWorx\OptionBase\Helper\Data as HelperBase;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\Data\GroupInterface;

class OptionValueAdvancedPricing extends AbstractModifier implements ModifierInterface
{
    const ADVANCED_PRICING_MODAL_INDEX = 'option_value_advanced_pricing_modal';
    const ADVANCED_PRICING_BUTTON_NAME = 'button_option_value_advanced_pricing';
    const MODAL_CONTENT                = 'content';
    const MODAL_FIELDSET               = 'fieldset';
    const OPTION_ADVANCED_PRICING      = 'option_value_advanced_pricing';

    const TEMPLATES_FORM_NAME = 'lr_option_value_templates_group_form';

    const OPTION_VALUE_TIER_PRICE    = 'values_tier_price';

    const OPTION_VALUE_TIER_PRICING    = 'values_tier_pricing';

   /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Helper
     */
    protected $helperBase;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var string
     */
    protected $form = self::FORM_NAME;

    /**
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param LocatorInterface $locator
     * @param Data $directoryHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param Helper $helper
     * @param HelperBase $helperBase
     * @param HttpRequest $request
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        Data $directoryHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        ArrayManager $arrayManager,
        Helper $helper,
        HelperBase $helperBase,
        HttpRequest $request,
        UrlInterface $urlBuilder
    ) {
        $this->storeManager          = $storeManager;
        $this->locator               = $locator;
        $this->directoryHelper       = $directoryHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository       = $groupRepository;
        $this->groupManagement       = $groupManagement;
        $this->arrayManager          = $arrayManager;
        $this->helper                = $helper;
        $this->helperBase            = $helperBase;
        $this->request               = $request;
        $this->urlBuilder            = $urlBuilder;
    }

    /**
     * Get sort order of modifier to load modifiers in the right order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return 100;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        if ($this->request->getRouteName() == 'mageworx_optiontemplates') {
            $this->form = static::TEMPLATES_FORM_NAME;
        }

        $this->addOptionAdvancedPricingModal();
        $this->addOptionAdvancedPricingButton();

        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName    = CustomOptions::CONTAINER_OPTION;

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
                $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
                [$optionContainerName]['children']['values']['children']['record']['children'],
                $this->getTierPriceHidden(250)
            );

        return $this->meta;
    }

    /**
     * Add modal window to configure option value's advanced pricing
     */
    protected function addOptionAdvancedPricingModal()
    {
        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::ADVANCED_PRICING_MODAL_INDEX => $this->getModalConfig(),
            ]
        );
    }


    /**
     * Get tier_price field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getTierPriceHidden($sortOrder)
    {
        $field[static::OPTION_VALUE_TIER_PRICING] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement'   => Hidden::NAME,
                        'dataScope'     => static::OPTION_VALUE_TIER_PRICE,
                        'dataType'      => Text::NAME,
                        'displayArea'   => 'insideGroup',
                        'sortOrder'     => $sortOrder,
                    ],
                ],
            ],
        ];
        return $field;
    }

    /**
     * Get advanced pricing modal config
     */
    protected function getModalConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'isTemplate'    => false,
                        'component'     => 'LR_CustomOptionPricing/component/modal-component',
                        'componentType' => Modal::NAME,
                        'dataScope'     => static::OPTION_ADVANCED_PRICING,
                        'provider'      => static::FORM_NAME . '.' . static::FORM_NAME . '_data_source',
                        'options'       => [
                            'title'   => __('Manage Option Value Sqare Area Pricing'),
                            'buttons' => [
                                [
                                    'text'    => __('Save & Close'),
                                    'class'   => 'action-primary',
                                    'actions' => [
                                        'save',
                                    ],
                                ],
                            ],
                        ],
                        'imports'       => [
                            'state' => '!index=' . static::MODAL_CONTENT . ':responseStatus',
                        ],
                    ],
                ],
            ],
            'children'  => [
                static::MODAL_CONTENT => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender'         => false,
                                'componentType'      => 'container',
                                'dataScope'          => 'data.product',
                                'externalProvider'   => 'data.product_data_source',
                                'ns'                 => static::FORM_NAME,
                                'behaviourType'      => 'edit',
                                'externalFilterMode' => true,
                                'currentProductId'   => $this->locator->getProduct()->getId(),
                            ],
                        ],
                    ],
                    'children'  => [
                        static::MODAL_FIELDSET => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'additionalClasses' => 'admin__fieldset-product-websites',
                                        'label'             => __('Option Value Sqare Area Pricing'),
                                        'collapsible'       => false,
                                        'componentType'     => Fieldset::NAME,
                                        'component'         => 'LR_CustomOptionPricing/component/fieldset',
                                        'dataScope'         => 'custom_pricing_data',
                                        'disabled'          => false
                                    ],
                                ],
                            ],
                            'children'  => $this->getPriceStructures()
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get special/customer group price structures
     *
     * @return array
     */
    protected function getPriceStructures()
    {
        $data = [];
        $data[static::OPTION_VALUE_TIER_PRICING] = $this->getTierPriceStructure();
        return $data;
    }

    /**
     * Get group price dynamic rows structure
     *
     * @return array
     */
    protected function getTierPriceStructure()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType'       => 'dynamicRows',
                        'label'               => __('Customer Group Sqare Area Price'),
                        'renderDefaultRecord' => false,
                        'recordTemplate'      => 'record',
                        'additionalClasses'   => 'apo-admin__field-widened',
                        'dataScope'           => '',
                        'dndConfig'           => [
                            'enabled' => false,
                        ],
                        'disabled'            => false,
                        'sortOrder'           => 20,
                    ],
                ],
            ],
            'children'  => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'is_collection' => true,
                                'component'     => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope'     => '',
                                'isTemplate'    => true,
                            ],
                        ],
                    ],
                    'children'  => [
                        'square_area'   => $this->getSquareAreaConfig(),
                        'group_id' => $this->getCustomerGroupsConfig(),
                        'price'             => $this->getPriceConfig(),
                        'actionDelete'      => $this->getActionDeleteConfig(),
                    ],
                ],
            ]
        ];
    }

    /**
     * Get customer groups config
     *
     * @return array
     */
    protected function getCustomerGroupsConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Customer Group'),
                        'dataScope'     => 'group_id',
                        'formElement'   => Select::NAME,
                        'componentType' => Field::NAME,
                        'dataType'      => Text::NAME,
                        'options'       => $this->getCustomerGroups(),
                        'value'         => '',//$this->getDefaultCustomerGroup(),
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get price config
     *
     * @return array
     */
    protected function getPriceConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Price'),
                        'dataScope'     => 'price',
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataType'      => Price::NAME,
                        'enableLabel'   => true,
                        'validation'    => [
                            'required-entry'             => true,
                            'validate-number'            => true,
                        ],
                        'addbefore'     => $this->locator->getStore()
                                                         ->getBaseCurrency()
                                                         ->getCurrencySymbol(),
                    ],
                ],
            ],
        ];
    }

    /**
     * Get action delete config
     *
     * @return array
     */
    protected function getActionDeleteConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => '',
                        'dataType'      => Text::NAME,
                        'componentType' => 'actionDelete',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Square Area config
     *
     * @return array
     */
    protected function getSquareAreaConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label'         => __('Square Area'),
                        'componentType' => Field::NAME,
                        'formElement'   => Input::NAME,
                        'dataType'      => Number::NAME,
                        'dataScope'     => 'square_area',
                        'validation'    => [
                            'required-entry'             => true,
                            'validate-greater-than-zero' => true,
                            'validate-number'            => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    protected function getCustomerGroups()
    {
        $customerGroups = [
            [
                'label' => __('Please Select'),
                'value' => '',
            ]
        ];

        /** @var GroupInterface[] $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $customerGroups;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    protected function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }


    /**
     * Add AdvancedPricing button
     */
    protected function addOptionAdvancedPricingButton()
    {
        $groupCustomOptionsName = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
        $optionContainerName    = CustomOptions::CONTAINER_OPTION;

        $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
        [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
            $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
            ['container_option']['children']['values']['children']['record']['children'],
            $this->getAdvancedPricingButtonConfig(250)
        );
    }

    /**
     * Get AdvancedPricing button config
     *
     * @param int $sortOrder
     * @param bool $additionalForGroup
     * @return array
     */
    protected function getAdvancedPricingButtonConfig($sortOrder, $additionalForGroup = false)
    {
        $params = [
            'provider'              => '${ $.provider }',
            'dataScope'             => '${ $.dataScope }',
            'buttonName'            => '${ $.name }',
            'formName'              => $this->form,
            'isSpecialPriceEnabled' => $this->helper->isSpecialPriceEnabled(),
            'isTierPriceEnabled'    => true
        ];

        if ($this->helperBase->checkModuleVersion('104.0.0')) {
            $params['__disableTmpl'] = [
                'provider'   => false,
                'dataScope'  => false,
                'buttonName' => false
            ];
        }

        $field[static::ADVANCED_PRICING_BUTTON_NAME] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'labelVisible'       => true,
                        'label'              => '',
                        'formElement'        => Container::NAME,
                        'componentType'      => Container::NAME,
                        'component'          => 'LR_CustomOptionPricing/component/button',
                        'additionalForGroup' => $additionalForGroup,
                        'displayArea'        => 'insideGroup',
                        'template'           => 'ui/form/components/button/container',
                        'elementTmpl'        => 'LR_CustomOptionPricing/button',
                        'buttonClasses'      => '',
                        'title'      => 'pricing',
                        'displayAsLink'      => false,
                        'sortOrder'          => $sortOrder,
                        'tooltip'            => [
                            'description' => __('Option Value Advanced Pricing')
                        ],
                        'mageworxAttributes' => $this->getEnabledAttributes(),
                        'actions'            => [
                            [
                                'targetName' => 'ns=' . $this->form . ', index='
                                    . static::ADVANCED_PRICING_MODAL_INDEX,
                                'actionName' => 'openModal',
                            ],
                            [
                                'targetName' => 'ns=' . $this->form . ', index='
                                    . static::ADVANCED_PRICING_MODAL_INDEX,
                                'actionName' => 'reloadModal',
                                'params'     => [$params],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $field;
    }

    /**
     * Get enabled attributes
     *
     * @return array
     */
    public function getEnabledAttributes()
    {
        $attributes = [];
        
        $attributes[static::OPTION_VALUE_TIER_PRICE] = '${ $.dataScope }' . '.' . static::OPTION_VALUE_TIER_PRICE;

        if ($this->helperBase->checkModuleVersion('104.0.0')) {
            $attributes['__disableTmpl'] = [
                static::OPTION_VALUE_TIER_PRICE => false,
            ];
        }

        return $attributes;
    }

    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly()
    {
        return false;
    }
}
