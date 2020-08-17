<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogStorefrontConnector\Model;

use Magento\CatalogDataExporter\Model\Indexer\ProductFeedIndexer;
use Magento\CatalogMessageBroker\Model\MessageBus\ProductsConsumer;
use Magento\CatalogStorefrontConnector\Helper\CustomStoreResolver;
use Magento\CatalogStorefrontConnector\Model\Publisher\CatalogEntityIdsProvider;
use Magento\CatalogStorefrontConnector\Model\Data\UpdatedEntitiesDataInterface;
use Magento\CatalogDataExporter\Model\Feed\Products as ProductsFeed;
use Magento\CatalogExport\Model\ChangedEntitiesMessageBuilder;
use Psr\Log\LoggerInterface;

/**
 * Consumer processes messages with store front products data
 */
class ProductsQueueConsumer
{
    const BATCH_SIZE = 100;

    /**
     * @var CatalogEntityIdsProvider
     */
    private $catalogEntityIdsProvider;

    /**
     * @var ProductsConsumer
     */
    private $productsConsumer;

    /**
     * @var ProductFeedIndexer
     */
    private $productFeedIndexer;

    /**
     * @var ChangedEntitiesMessageBuilder
     */
    private $messageBuilder;

    /**
     * @var ProductsFeed
     */
    private $productsFeed;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomStoreResolver
     */
    private $storeResolver;

    /**
     * @param ProductsConsumer $productsConsumer
     * @param ProductFeedIndexer $productFeedIndexer
     * @param ChangedEntitiesMessageBuilder $messageBuilder
     * @param CustomStoreResolver $storeResolver
     * @param LoggerInterface $logger
     * @param ProductsFeed $productsFeed
     * @param CatalogEntityIdsProvider $catalogEntityIdsProvider
     */
    public function __construct(
        ProductsConsumer $productsConsumer,
        ProductFeedIndexer $productFeedIndexer,
        ChangedEntitiesMessageBuilder $messageBuilder,
        CustomStoreResolver $storeResolver,
        LoggerInterface $logger,
        ProductsFeed $productsFeed,
        CatalogEntityIdsProvider $catalogEntityIdsProvider
    ) {
        $this->catalogEntityIdsProvider = $catalogEntityIdsProvider;
        $this->productsConsumer = $productsConsumer;
        $this->productFeedIndexer = $productFeedIndexer;
        $this->messageBuilder = $messageBuilder;
        $this->productsFeed = $productsFeed;
        $this->logger = $logger;
        $this->storeResolver = $storeResolver;
    }

    /**
     * Process collected product IDs for update
     *
     * @param UpdatedEntitiesDataInterface $message
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated React on events triggered by plugins to push data to SF storage
     */
    public function processMessages(UpdatedEntitiesDataInterface $message): void
    {
        $storeId = $message->getStoreId();
        $storeCode = $this->storeResolver->resolveStoreCode($storeId);
        $ids = $message->getEntityIds();

        //TODO: remove ad-hoc solution after moving events to corresponding export service
        if (empty($ids)) {
            $this->productFeedIndexer->executeFull();
            foreach ($this->catalogEntityIdsProvider->getProductIds($storeId) as $idsChunk) {
                $ids[] = $idsChunk;
            }
        } else {
            //TODO: move this to plugins?
            $this->productFeedIndexer->executeList($ids);
        }

        $deletedIds = [];
        foreach ($this->productsFeed->getDeletedByIds($ids, array_filter([$storeCode])) as $product) {
            $deletedIds[] = $product['productId'];
            unset($ids[$product['productId']]);
        }

        if (!empty($ids)) {
            $this->passMessage(
                ProductsConsumer::PRODUCTS_UPDATED_EVENT_TYPE,
                $ids,
                $storeCode
            );
        }

        if (!empty($deletedIds)) {
            $this->passMessage(
                ProductsConsumer::PRODUCTS_DELETED_EVENT_TYPE,
                $deletedIds,
                $storeCode
            );
        }
    }

    /**
     * Publish deleted or updated message
     *
     * @param string $eventType
     * @param int[] $ids
     * @param string $storeCode
     */
    private function passMessage(string $eventType, array $ids, string $storeCode)
    {
        foreach (array_chunk($ids, self::BATCH_SIZE) as $idsChunk) {
            if (!empty($idsChunk)) {
                $message = $this->messageBuilder->build(
                    $ids,
                    $eventType,
                    $storeCode
                );
                try {
                    $this->productsConsumer->processMessage($message);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }
}
