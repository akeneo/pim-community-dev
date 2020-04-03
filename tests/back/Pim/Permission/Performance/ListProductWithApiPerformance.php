<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Performance;

use Blackfire\Profile;
use Blackfire\Profile\Configuration;
use PHPUnit\Framework\Assert;

class ListProductWithApiPerformance extends AbstractApiPerformance
{
    /**
     * We check SQL queries to avoid n+1 queries when filtering data.
     * We have to check the wall time also.
     *
     * Blackfire adds a non-negligeable overhead, but the target is to have 50 products/sec on the reference catalog.
     * As the overhead is constant, it's not a problem but we have to take it in account.
     */
    public function test_that_exporting_products_with_api_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();

        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Export products with the API');

        $profileConfig->assert('metrics.sql.queries.count < 55', 'SQL queries');
        $profileConfig->assert('main.wall_time < 10s', 'Total time');
        $profileConfig->assert('main.peak_memory < 100mb', 'Memory');

        $client = $this->createAuthenticatedClient();

        /** @var Profile $profile */
        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
            $client->request('GET', 'api/rest/v1/products?limit=100');
        });

        $response = $client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());
        $products = json_decode($response->getContent(), true)['_embedded']['items'];
        Assert::assertSame(100, count($products));

        echo PHP_EOL. 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
    }
}
