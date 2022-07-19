<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Performance;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Blackfire\Profile\Configuration;
use Blackfire\Profile\Metric;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ImportVariantProductsWithApiPerformance extends AbstractApiPerformance
{
    private const VARIANT_PRODUCT_COUNT = 8;
    private const CATEGORY_COUNT = 3;

    /**
     * This method will patch 8 variant products through API by updating categories that are randomly picked.
     * Each variant product belongs to a different family.
     * We check if this import is performant, regarding the main time, memory, SQL counts and some completeness
     * calculation metrics.
     */
    public function test_that_importing_products_with_api_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Import variant products with the API');

        $profileConfig->defineMetric(
            new Metric('completeness_calculation', '=Akeneo\\Pim\\Enrichment\\Component\\Product\\Completeness\\CompletenessCalculator::fromProductUuids')
        );

        // Original value was 966.
        $profileConfig->assert('metrics.sql.queries.count < 1065', 'SQL queries');
        // Original value: 5.7s
        $profileConfig->assert('main.wall_time < 8s', 'Total time');
        // Original value: 27.9MB
        $profileConfig->assert('main.peak_memory < 45mb', 'Memory');
        // Ensure only 1 completeness calculation is done
        $profileConfig->assert('metrics.completeness_calculation.count == 1', 'Completeness calculation calls');
        // Ensure only 2 calls are done to ES
        $profileConfig->assert('metrics.http.curl.requests.count == 5', 'Queries to ES');
        // Original value: 329ms
        $profileConfig->assert('metrics.completeness_calculation.wall_time < 420ms', 'Completeness calculation time');

        $client = $this->createAuthenticatedClient('data_source');

        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
            $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);
            $client->request('PATCH', 'api/rest/v1/products', [], [], [], $this->getBody());
        });

        $response = $client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());

        echo PHP_EOL . 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
    }

    private function getVariantProductIdentifiers(int $limit)
    {
        $sql = <<<SQL
SELECT MIN(product.identifier) AS identifier
FROM pim_catalog_product product
WHERE product.product_model_id IS NOT NULL
GROUP BY family_id
LIMIT ${limit}
SQL;
        $variantProductIdentifiers = [];
        $rows = $this->get('database_connection')->executeQuery($sql)->fetchAll();
        foreach ($rows as $row) {
            $variantProductIdentifiers[] = $row['identifier'];
        }

        return $variantProductIdentifiers;
    }

    private function getCategoryCodes(int $limit)
    {
        $sql = <<<SQL
SELECT category.code AS code
FROM pim_catalog_category category
LIMIT ${limit}
SQL;
        $categoryCodes = [];
        $rows = $this->get('database_connection')->executeQuery($sql)->fetchAll();
        foreach ($rows as $row) {
            $categoryCodes[] = $row['code'];
        }

        return $categoryCodes;
    }

    private function getBody(): string
    {
        $categoryCodes = $this->getCategoryCodes(self::CATEGORY_COUNT);
        $categoryCodesAsString = json_encode($categoryCodes);

        return join("\n", array_map(function ($variantProductIdentifier) use ($categoryCodesAsString) {
            return '{"identifier": "' . $variantProductIdentifier . '", "categories": ' . $categoryCodesAsString . '}';
        }, $this->getVariantProductIdentifiers(self::VARIANT_PRODUCT_COUNT)));
    }
}
