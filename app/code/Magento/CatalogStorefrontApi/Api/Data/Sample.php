<?php
# Generated by the Magento PHP proto generator.  DO NOT EDIT!

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogStorefrontApi\Api\Data;

/**
 * Autogenerated description for Sample class
 *
 * phpcs:disable Magento2.PHP.FinalImplementation
 * @SuppressWarnings(PHPMD)
 * @SuppressWarnings(PHPCPD)
 */
final class Sample implements SampleInterface
{

    /**
     * @var \Magento\CatalogStorefrontApi\Api\Data\MediaResourceInterface
     */
    private $resource;

    /**
     * @var string
     */
    private $sortOrder;
    
    /**
     * @inheritdoc
     *
     * @return \Magento\CatalogStorefrontApi\Api\Data\MediaResourceInterface|null
     */
    public function getResource(): ?\Magento\CatalogStorefrontApi\Api\Data\MediaResourceInterface
    {
        return $this->resource;
    }
    
    /**
     * @inheritdoc
     *
     * @param \Magento\CatalogStorefrontApi\Api\Data\MediaResourceInterface $value
     * @return void
     */
    public function setResource(\Magento\CatalogStorefrontApi\Api\Data\MediaResourceInterface $value): void
    {
        $this->resource = $value;
    }
    
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getSortOrder(): string
    {
        return (string) $this->sortOrder;
    }
    
    /**
     * @inheritdoc
     *
     * @param string $value
     * @return void
     */
    public function setSortOrder(string $value): void
    {
        $this->sortOrder = $value;
    }
}
