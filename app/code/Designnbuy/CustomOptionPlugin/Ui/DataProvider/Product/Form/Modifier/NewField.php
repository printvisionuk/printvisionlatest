<?php
namespace Designnbuy\CustomOptionPlugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;
use \Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;  // by Vips

class NewField extends AbstractModifier
{
    const DISCOUNT_FREQUENCY_FIELD = 'square_area_pricing'; //attribute code

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName; 

    /**
     * @var array
     */
    protected $customerGroup;   // by Vips

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        GroupCollection $customerGroup, // by Vips
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->customerGroup = $customerGroup; // by Vips
        $this->scopeName = $scopeName;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::DISCOUNT_FREQUENCY_FIELD;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        
        $frequencyData = $model->getSquareAreaPricing();

        if ($frequencyData) {
            $frequencyData = json_decode($frequencyData, true);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::DISCOUNT_FREQUENCY_FIELD;
            $data = $this->arrayManager->set($path, $data, $frequencyData);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initDiscountFrequencyFields();
        return $this->meta;
    }


    protected function initDiscountFrequencyFields()
    {
        $frequencyPath = $this->arrayManager->findPath(
            self::DISCOUNT_FREQUENCY_FIELD,
            $this->meta,
            null,
            'children'
        );

        if ($frequencyPath) {
            $this->meta = $this->arrayManager->merge(
                $frequencyPath,
                $this->meta,
                $this->initFrquencyFieldStructure($frequencyPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($frequencyPath, 0, -3)
                . '/' . self::DISCOUNT_FREQUENCY_FIELD,
                $this->meta,
                $this->arrayManager->get($frequencyPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($frequencyPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }   


    protected function initFrquencyFieldStructure($frequencyPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Square Area Pricing'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($frequencyPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'square_area' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Square Area'),
                                        'dataScope' => 'square_area',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],
                        // 'measurement_units' => [
                        //     'arguments' => [
                        //         'data' => [
                        //             'config' => [
                        //                 'formElement' => Select::NAME,
                        //                 'componentType' => Field::NAME,
                        //                 'dataType' => Text::NAME,
                        //                 'label' => __('Measurement Units'),
                        //                 'dataScope' => 'measurement_units',
                        //                 'options' => $this->UnitsType(),
                        //             ],
                        //         ],
                        //     ],
                        // ],
                        // Code Start by Vips 
                        'group_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'group_id',
                                        'label' => __('Customer Group'),
                                        'options' => $this->getCustomerGroups(),
                                        'require' => '1'
                                    ],
                                ],
                            ],
                        ],
                        // Code End by Vips 
                        'price' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Price'),
                                        'dataScope' => 'price',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function UnitsType()
    {
        $regionOptions = array();
        $regionOptions[] = ['label' =>  'mm', 'value' => 'mm'];
        $regionOptions[] = ['label' =>  'meter', 'value' => 'meter'];
        $regionOptions[] = ['label' =>  'feet', 'value' => 'feet'];
        return $regionOptions;
    }

    // Code Start by Vips 
    public function getCustomerGroups() {
        $customerGroups = $this->customerGroup->toOptionArray();
        array_unshift($customerGroups, array('value'=>'', 'label'=>'Please Select'));
        return $customerGroups;
    }
    // Code Ends by Vips 
}
?>