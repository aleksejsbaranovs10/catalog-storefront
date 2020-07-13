<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$eavConfig = Bootstrap::getObjectManager()->get(Config::class);
Resolver::getInstance()->requireDataFixture('Magento/Eav/_files/eav_attributes_with_custom_attribute_set.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$storeManager = $objectManager->get(\Magento\Store\Model\StoreManager::class);
$store = $storeManager->getStore('default');
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$installer = $objectManager->get(\Magento\Catalog\Setup\CategorySetup::class);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'new_attribute_set_storefront');
$multiselectAttribute = $eavConfig->getAttribute(Product::ENTITY, 'multiselect_attribute');

$multiselectOptionsIds = $objectManager->create(Collection::class)
    ->setAttributeFilter($multiselectAttribute->getId())
    ->getAllIds();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class)
    ->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Simple Product 1')
    ->setSku('simple1')
    ->setPrice(10)
    ->setMultiselectAttribute($multiselectOptionsIds[0])
    ->setTextAttribute('text Attribute test')
    ->setTextAreaAttribute('text Area Attribute test')
    ->setBooleanAttribute(1)
    ->setDateAttribute(date('Y-m-d'))
    ->setDateTimeAttribute(date('Y-m-d H:i:s'))
    ->setTextEditorAttribute('text Editor Attribute test')
    ->setImageAttribute('imagepath')
    ->setPriceAttribute(100)
    ->setWeeeAttribute(
        [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 10.00, 'delete' => '']]
    )
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);
$productRepository->save($product);
