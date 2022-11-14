<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Performance;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Ramsey\Uuid\Uuid;

class GetCatalogProductsPerformance extends PerformanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
        $this->createUser('admin', ['IT support'], ['ROLE_ADMINISTRATOR']);
    }

    public function testThatRetrievingProductsIsPerformant(): void
    {
        $this->config->setTitle('Get products from a Catalog');
        $this->config->assert('main.wall_time < 350ms', 'Wall time');
        $this->config->assert('main.peak_memory < 10mb', 'Peak memory');
        $this->config->assert('metrics.sql.queries.count < 15', 'SQL queries');
        $this->config->assert('metrics.http.curl.requests.count == 1', 'ES queries');

        $this->logAs('admin'); // Creating products requires an authenticated user with higher permissions
        $this->createProduct(Uuid::fromString('8985de43-08bc-484d-aee0-4489a56ba02d'), [new SetEnabled(true)]);
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $profile = $this->assertBlackfire($this->config, function () use ($client): void {
            $client->request('GET', '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/products');
        });

        $cost = $profile->getMainCost();

        echo PHP_EOL . PHP_EOL;
        echo $profile->getUrl() . PHP_EOL;
        echo \sprintf('Wall time: %.2fms', $cost->getWallTime() / 1000) . PHP_EOL;
        echo \sprintf('Peak memory: %.2fMB', $cost->getPeakMemoryUsage() / 1000000) . PHP_EOL;
        echo \sprintf('SQL Queries: %d', \is_countable($profile->getSqls()) ? \count($profile->getSqls()) : 0) . PHP_EOL;
        echo \sprintf('HTTP Requests: %d', \is_countable($profile->getHttpRequests()) ? \count($profile->getHttpRequests()) : 0) . PHP_EOL;
    }
}
