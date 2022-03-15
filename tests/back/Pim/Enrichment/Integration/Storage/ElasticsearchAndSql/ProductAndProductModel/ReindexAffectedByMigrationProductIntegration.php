<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class ReindexAffectedByMigrationProductIntegration extends TestCase
{
    public function test_it_deletes_and_reindex_affected_by_migration_product()
    {
        $wasColumnDropped = false;
        if ($this->uuidColumnExists()) {
            $this->dropUuidColumn();
            $wasColumnDropped = true;
        }

        $product = $this->getProductBuilder()->createProduct('foo');
        $this->getProductSaver()->save($product);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(1, $this->getElasticSearchClient()->count([])['count']);
        $formerProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];

        $this->addUuidColumn();

        $this->getElasticSearchProductProjection()->clearCache();
        $this->getConnection()->executeQuery(strtr(<<<SQL
        UPDATE pim_catalog_product
        SET uuid=UUID_TO_BIN('{uuid}');
        SQL, ['{uuid}' => Uuid::uuid4()->toString()]));
        $this->getProductSaver()->save($product, ['force_save' => true]);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(1, $this->getElasticSearchClient()->count([])['count']);
        $newProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];

        Assert::assertNotEquals($formerProductId, $newProductId);

        if (!$wasColumnDropped) {
            $this->dropUuidColumn();
        }
    }

    public function test_it_deletes_products_after_uuid_indexation()
    {
        $wasColumnAdded = false;
        if (!$this->uuidColumnExists()) {
            $this->addUuidColumn();
            $wasColumnAdded = true;
        }

        $product = $this->getProductBuilder()->createProduct('foo');
        $this->getProductSaver()->save($product);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(1, $this->getElasticSearchClient()->count([])['count']);

        $this->getProductRemover()->remove($product);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::assertSame(0, $this->getElasticSearchClient()->count([])['count']);

        if ($wasColumnAdded) {
            $this->dropUuidColumn();
        }
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

    private function getElasticSearchProductProjection()
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getProductRemover(): RemoverInterface
    {
        return $this->get('pim_catalog.remover.product');
    }

    private function addUuidColumn()
    {
        $this->getConnection()->executeQuery('ALTER TABLE pim_catalog_product ADD uuid BINARY(16) DEFAULT NULL AFTER id, LOCK=NONE, ALGORITHM=INPLACE');
    }

    private function dropUuidColumn()
    {
        $this->getConnection()->executeQuery('ALTER TABLE pim_catalog_product DROP COLUMN uuid, LOCK=NONE, ALGORITHM=INPLACE');
    }

    private function uuidColumnExists(): bool
    {
        $rows = $this->getConnection()->fetchAllAssociative('SHOW COLUMNS FROM pim_catalog_product LIKE "uuid"');

        return count($rows) >= 1;
    }
}
