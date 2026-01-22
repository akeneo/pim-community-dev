<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use PHPUnit\Framework\Assert;

class UpdateIndexMappingWithoutDowntimeIntegration extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $indexHost = $this->getParameter('index_hosts');
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);
        $this->client = $clientBuilder->build();
        $this->loadData();
    }

    public function test_it_moves_the_index_and_the_pqb_still_works()
    {
        /** @var Loader $indexConfigurationLoader */
        $indexConfigurationLoader = $this->get('akeneo_elasticsearch.client.product_and_product_model.index_configuration.files');
        $productAndProductModelIndexAlias = $this->getParameter('product_and_product_model_index_name');

        $aliasesBeforeMigration = array_map(fn (array $index) => $index['alias'], $this->client->cat()->aliases(['format' => 'json'])->asArray());
        $indexNameBeforeMigration = $this->getIndexNameFromAlias($productAndProductModelIndexAlias);

        /** @var ProductQueryBuilderFactory $pqb */
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory_for_reading_purpose');
        Assert::assertNotNull($indexNameBeforeMigration);
        Assert::assertEquals(1, $pqb->create()->execute()->count());

        /** @var UpdateIndexMappingWithoutDowntime $updateIndexMapping */
        $updateIndexMapping = $this->get('Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime');
        $updateIndexMapping->execute(
            $productAndProductModelIndexAlias,
            'temporary_index',
            'migrated_index_name' . uniqid(),
            $indexConfigurationLoader->load(),
            static fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'entity_updated' => ['gt' => $referenceDatetime->format('c')]
                ],
            ],
        );

        $aliasesAfterMigration = array_map(fn (array $index) => $index['alias'], $this->client->cat()->aliases(['format' => 'json'])->asArray());
        $indexNameAfterMigration = $this->getIndexNameFromAlias($productAndProductModelIndexAlias);

        Assert::assertEqualsCanonicalizing($aliasesBeforeMigration, $aliasesAfterMigration);
        Assert::assertNotEquals($indexNameBeforeMigration, $indexNameAfterMigration);
        Assert::assertEquals(1, $pqb->create()->execute()->count());
    }

    public function test_it_is_idempotent()
    {
        /** @var Loader $indexConfigurationLoader */
        $indexConfigurationLoader = $this->get('akeneo_elasticsearch.client.product_and_product_model.index_configuration.files');
        $productAndProductModelIndexAlias = $this->getParameter('product_and_product_model_index_name');

        $indexNameBeforeMigration = $this->getIndexNameFromAlias($productAndProductModelIndexAlias);

        /** @var UpdateIndexMappingWithoutDowntime $updateIndexMapping */
        $updateIndexMapping = $this->get('Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMappingWithoutDowntime');
        $updateIndexMapping->execute(
            $productAndProductModelIndexAlias,
            'temporary_index_alias',
            'migrated_index_name' . uniqid(),
            $indexConfigurationLoader->load(),
            static fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'entity_updated' => ['gt' => $referenceDatetime->format('c')]
                ],
            ],
        );

        $indexNameAfterFirstMigration = $this->getIndexNameFromAlias($productAndProductModelIndexAlias);

        $updateIndexMapping->execute(
            $productAndProductModelIndexAlias,
            'temporary_index_alias',
            'migrated_index_name' . uniqid(),
            $indexConfigurationLoader->load(),
            static fn (\DateTimeImmutable $referenceDatetime) => [
                'range' => [
                    'entity_updated' => ['gt' => $referenceDatetime->format('c')]
                ],
            ],
        );

        $indexNameAfterSecondMigration = $this->getIndexNameFromAlias($productAndProductModelIndexAlias);

        Assert::assertNotEquals($indexNameBeforeMigration, $indexNameAfterFirstMigration);
        Assert::assertNotEquals($indexNameBeforeMigration, $indexNameAfterSecondMigration);
        Assert::assertNotEquals($indexNameAfterFirstMigration, $indexNameAfterSecondMigration);
    }

    private function getIndexNameFromAlias(string $indexAlias): ?string
    {
        $aliases = $this->client->indices()->getAlias(['name' => $indexAlias])->asArray();

        return array_keys($aliases)[0] ?? null;
    }

    private function loadData(): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('product1');
        $this->get('pim_catalog.updater.product')->update($product, []);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
