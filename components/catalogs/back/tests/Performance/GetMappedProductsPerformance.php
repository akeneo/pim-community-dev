<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Performance;

use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Ramsey\Uuid\Uuid;

class GetMappedProductsPerformance extends PerformanceTestCase
{
    private const NUMBER_OF_PRODUCTS = 100;
    private const NUMBER_OF_MAPPED_ATTRIBUTES = 100;

    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
    }

    public function testThatRetrievingProductsIsPerformant(): void
    {
        $this->config->setTitle('Get mapped products from a Catalog');
        $this->config->assert('main.wall_time < 50ms', 'Wall time');
        $this->config->assert('main.peak_memory < 1mb', 'Peak memory');
        $this->config->assert('metrics.sql.queries.count <= 20', 'SQL queries');
        $this->config->assert('metrics.http.curl.requests.count <= 2', 'Network requests');

        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $this->loadFixtures();

        $this->logAs('shopifi');

        // first call to be sure the cache is warmed up
        $client->request('GET', '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products');

        $profile = $this->assertBlackfire($this->config, function () use ($client): void {
            $client->request('GET', '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapped-products');
        });

        $cost = $profile->getMainCost();

        echo PHP_EOL . PHP_EOL;
        echo $profile->getUrl() . PHP_EOL;
        echo \sprintf('Wall time: %.2fms', $cost->getWallTime() / 1000) . PHP_EOL;
        echo \sprintf('Peak memory: %.2fMB', $cost->getPeakMemoryUsage() / 1000000) . PHP_EOL;
        echo \sprintf('SQL Queries: %d', \count($profile->getSqls())) . PHP_EOL;
        echo \sprintf('HTTP Requests: %d', \count($profile->getHttpRequests())) . PHP_EOL;
    }

    private function loadFixtures(): void
    {
        $this->logAs('admin'); // Creating products requires an authenticated user with higher permissions

        $this->createChannel('print', ['en_US', 'fr_FR']);
        $this->createAttributes();
        $this->createProducts();
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->commandBus->execute(new UpdateProductMappingSchemaCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            \json_decode($this->getProductMappingSchemaRaw(), false, 512, JSON_THROW_ON_ERROR),
        ));

        $this->setCatalogProductMapping('db1079b6-f397-4a6a-bae4-8658e64ad47c', $this->createCatalogProductMapping());
    }

    private function createAttributes(): void
    {
        for ($i = 0; $i < self::NUMBER_OF_MAPPED_ATTRIBUTES; $i++) {
            $this->createAttribute([
                'code' => \sprintf('source_%d', $i),
                'type' => 'pim_catalog_text',
                'scopable' => true,
                'localizable' => true,
            ]);
        }
    }

    private function createProducts(): void
    {
        for ($i = 0; $i < self::NUMBER_OF_PRODUCTS; $i++) {
            $attributes = [];
            for ($j = 0; $j < self::NUMBER_OF_MAPPED_ATTRIBUTES; $j++) {
                $attributes[] = new SetTextValue(
                    \sprintf('source_%d', $j),
                    'print',
                    'en_US',
                    \sprintf('value_%d_%d', $i, $j),
                );
            }
            $this->createProduct(Uuid::uuid4(), $attributes);
        }
    }

    /**
     * @return array<string, array{source: string, scope: string|null, locale: string|null}>
     */
    private function createCatalogProductMapping(): array
    {
        $productMapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
        ];

        for ($i = 0; $i < self::NUMBER_OF_MAPPED_ATTRIBUTES; $i++) {
            $productMapping[\sprintf('target_%d', $i)] = [
                'source' => \sprintf('source_%d', $i),
                'scope' => 'print',
                'locale' => 'en_US',
            ];
        }

        return $productMapping;
    }

    private function getProductMappingSchemaRaw(): string
    {
        $productMappingSchemaTargets = [];
        for ($i = 0; $i < self::NUMBER_OF_MAPPED_ATTRIBUTES; $i++) {
            $productMappingSchemaTargets[\sprintf('target_%d', $i)] = ['type' => 'string'];
        }

        return \json_encode([
            '$id' => 'https://example.com/product',
            '$schema' => 'https://api.akeneo.com/mapping/product/0.0.2/schema',
            '$comment' => 'My first schema !',
            'title' => 'Product Mapping',
            'description' => 'JSON Schema describing the structure of products expected by our application',
            'type' => 'object',
            'properties' => [
                'uuid' => ['type' => 'string'],
                ...$productMappingSchemaTargets,
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
