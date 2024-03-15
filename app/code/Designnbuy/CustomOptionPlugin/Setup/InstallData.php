<?php
namespace Designnbuy\CustomOptionPlugin\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    	/**
	 * @param GroupFactory $groupFactory 
	 */
	public function __construct(
		EavSetupFactory $eavSetupFactory
	) {
		$this->eavSetupFactory = $eavSetupFactory;
	}

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'custom_pricing_logic');
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Product::ENTITY,
		'custom_pricing_logic',
			[
				'type' => 'int',
				'backend' => '',
				'frontend' => '',
				'label' => 'Custom Pricing Logic',
				'note' => '',
				'input' => 'boolean',
				'class' => '',
				'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => true,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'sort_order' => 100,
				'apply_to' => 'simple,virtual,bundle,downloadable,grouped,configurable',
			]
		);

		$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'square_area_pricing');
		$eavSetup->addAttribute(
		\Magento\Catalog\Model\Product::ENTITY,
		'square_area_pricing',
			[
				//'group' => 'General',
				'type' => 'text',
				'frontend' => '',
				'label' => 'Square Area Pricing',
				'input' => 'textarea',
				'class' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => true,
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => false,
				'unique' => false,
				'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped'
			]
		);

		
		$canvasattributeGroupCode = $eavSetup->convertToAttributeGroupCode("Square Area Pricing");

		$entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$attributeSetCollection = $objectManager->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection');
		$attributeSetCollection->setEntityTypeFilter($entityTypeId);
		$attributeSetCollection->load();
		
		foreach ($attributeSetCollection as $attributeSet) {
			$canvasattributeGroup = $eavSetup->getAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSet->getAttributeSetName(), $canvasattributeGroupCode, 'attribute_group_id');
			if($canvasattributeGroup == ""){
				$eavSetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSet->getAttributeSetName(), 'Square Area Pricing', 200);
			}
			$eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSet->getAttributeSetName(), 'Square Area Pricing', 'custom_pricing_logic', 10);
			$eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, $attributeSet->getAttributeSetName(), 'Square Area Pricing', 'square_area_pricing', 20);
		}

    }
}
?>