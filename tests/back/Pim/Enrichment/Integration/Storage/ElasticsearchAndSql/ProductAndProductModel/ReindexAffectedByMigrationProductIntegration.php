<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

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
        Assert::equalTo($this->getElasticSearchClient()->count([])['count'], 1);
        $formerProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];

        $this->addUuidColumn();

        $this->getElasticSearchProductProjection()->clearCache();
        $this->getProductSaver()->save($product, ['force_save' => true]);
        $this->getElasticSearchClient()->refreshIndex();
        Assert::equalTo($this->getElasticSearchClient()->count([])['count'], 1);
        $newProductId = $this->getElasticSearchClient()->search([])['hits']['hits'][0]['_id'];

        Assert::assertNotEquals($formerProductId, $newProductId);

        if (!$wasColumnDropped) {
            $this->dropUuidColumn();
        }
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getProductSaver(): ProductSaver
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
