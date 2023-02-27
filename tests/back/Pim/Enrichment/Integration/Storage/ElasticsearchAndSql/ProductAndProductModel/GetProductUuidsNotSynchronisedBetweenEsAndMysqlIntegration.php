<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetProductUuidsNotSynchronisedBetweenEsAndMysql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsNotSynchronisedBetweenEsAndMysqlIntegration extends TestCase
{
    public function test_it_returns_nothing_if_every_product_is_synchronized(): void
    {
        $this->assertResults(
            $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100),
            []
        );
    }

    public function test_it_gets_desynchronized_product_uuids(): void
    {
        $productUuids = $this->getRandomProductUuids(5);
        $this->desynchronizeProducts($productUuids);

        $this->assertResults(
            $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100),
            $productUuids
        );
    }

    public function test_it_gets_non_indexed_product_uuids(): void
    {
        $productUuids = $this->getRandomProductUuids(5);
        $this->deleteProductDocumentsFromIndex($productUuids);

        $this->assertResults(
            $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100),
            $productUuids
        );
    }

    public function test_it_gets_desynchronized_and_non_indexed_product_uuids(): void
    {
        $desynchronizedProductUuids = $this->getRandomProductUuids(2);
        $this->desynchronizeProducts($desynchronizedProductUuids);

        $deletedProductUuids = $this->getRandomProductUuids(2);
        $this->deleteProductDocumentsFromIndex($deletedProductUuids);

        $this->assertResults(
            $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100),
            \array_unique(\array_merge($desynchronizedProductUuids, $deletedProductUuids))
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
        $this->getESClient()->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getProductUuidsNotSynchronisedBetweenEsAndMysql(): GetProductUuidsNotSynchronisedBetweenEsAndMysql
    {
        return $this->get(GetProductUuidsNotSynchronisedBetweenEsAndMysql::class);
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getESClient(): Client
    {
        return $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    private function getRandomProductUuids(int $count = 1): array
    {
        $uuids = $this->getDbConnection()->executeQuery(
            \sprintf('SELECT uuid FROM pim_catalog_product ORDER BY rand() LIMIT %d', $count)
        )->fetchFirstColumn();

        return \array_map(
            static fn (string $uuid): UuidInterface => Uuid::fromBytes($uuid),
            $uuids
        );
    }

    private function deleteProductDocumentsFromIndex(array $productUuids): void
    {
        $this->getESClient()->bulkDelete(
            \array_map(
                static fn (UuidInterface $uuid): string => \sprintf('product_%s', $uuid->toString()),
                $productUuids
            )
        );
        $this->getESClient()->refreshIndex();
    }

    private function desynchronizeProducts(array $productUuids): void
    {
        $this->getDbConnection()->executeStatement(
            'UPDATE pim_catalog_product SET updated = DATE_ADD(updated, INTERVAL 1 DAY) WHERE uuid IN (:uuids)',
            ['uuids' => \array_map(
                static fn (UuidInterface $uuid): string => $uuid->getBytes(),
                $productUuids
            )],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function assertResults(iterable $actualResults, array $expectedResults): void
    {
        $actualResults = [...$actualResults][0];
        Assert::assertContainsOnlyInstancesOf(UuidInterface::class, $actualResults);
        Assert::assertEqualsCanonicalizing($actualResults, $expectedResults);
    }
}
