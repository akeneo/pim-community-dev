<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class ReindexAffectedByMigrationProductIntegration extends TestCase
{
    public function test_it_deletes_and_reindexes_products_affected_by_migration()
    {
        $product = $this->createProduct('foo', false);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(1, $this->getElasticSearchClient()->count([])['count']);
        $formerProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];
        Assert::assertSame('product_' . $product->getId(), $formerProductId);

        $uuid = Uuid::uuid4()->toString();
        $this->getConnection()->executeQuery(<<<SQL
            UPDATE pim_catalog_product
            SET uuid=UUID_TO_BIN(:uuid);
            SQL,
            ['uuid' => $uuid]
        );
        $this->getProductSaver()->save($product, ['force_save' => true]);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(1, $this->getElasticSearchClient()->count([])['count']);
        $newProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];
        Assert::assertSame('product_' . $uuid, $newProductId);
    }

    public function test_it_deletes_products_after_uuid_indexation()
    {
        $productWithoutUuid = $this->createProduct('product_without_uuid', false);
        $productWithUuid = $this->createProduct('product_with_uuid');

        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(2, $this->getElasticSearchClient()->count([])['count']);

        $this->getProductRemover()->removeAll([$productWithoutUuid, $productWithUuid]);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(0, $this->getElasticSearchClient()->count([])['count']);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getProductSaver(): SaverInterface
    {
        return $this->get('pim_catalog.saver.product');
    }

    private function getElasticSearchClient(): Client
    {
        return $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    private function getProductBuilder(): ProductBuilderInterface
    {
        return $this->get('pim_catalog.builder.product');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getProductRemover(): BulkRemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function createProduct(string $identifier, $withUuid = true): ProductInterface
    {
        $product = $this->getProductBuilder()->createProduct($identifier);
        $this->getProductSaver()->save($product);

        if (false === $withUuid) {
            $connection = $this->getConnection();
            $uuid = $connection->executeQuery(
                'SELECT BIN_TO_UUID(uuid) from pim_catalog_product WHERE id = :id',
                ['id' => $product->getId()]
            )->fetchOne();
            $productIndexer = $this->get('pim_catalog.elasticsearch.indexer.product');
            $productIndexer->removeFromProductUuids([Uuid::fromString($uuid)]);
            $connection->executeQuery(
                'UPDATE pim_catalog_product SET uuid = NULL WHERE id = :id',
                ['id' => $product->getId()]
            );
            $productIndexer->indexFromProductIdentifiers([$product->getIdentifier()]);
        }

        return $product;
    }
}
