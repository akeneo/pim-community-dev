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
class ImportProductModelsWithApiPerformance extends AbstractApiPerformance
{
    private const PRODUCT_MODEL_COUNT = 10;
    private const CATEGORY_COUNT = 3;

    /**
     * This method will patch 10 product models through API by updating categories that are randomly picked.
     * We check if this import is performant, regarding the main time, memory, SQL counts and some completeness
     * calculation metrics.
     */
    public function test_that_importing_product_models_with_api_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Import product models with the API');

        $profileConfig->defineMetric(
            new Metric('completeness_calculation', '=Akeneo\\Pim\\Enrichment\\Component\\Product\\Completeness\\CompletenessCalculator::fromProductIdentifiers')
        );

        // Original value was 1236.
        $profileConfig->assert('metrics.sql.queries.count < 1335', 'SQL queries');
        // Original value: 10.9s
        $profileConfig->assert('main.wall_time < 13s', 'Total time');
        // Original value: 39.1MB
        $profileConfig->assert('main.peak_memory < 80mb', 'Memory');
        // Ensure only 1 completeness calculation is done
        $profileConfig->assert('metrics.completeness_calculation.count == 1', 'Completeness calculation calls');
        // Ensure only 2 calls are done to ES (1 for product model, 1 for products)
        $profileConfig->assert('metrics.http.curl.requests.count == 5', 'Queries to ES');
        // Original value: 845ms
        $profileConfig->assert('metrics.completeness_calculation.wall_time < 1200ms', 'Completeness calculation time');

        $client = $this->createAuthenticatedClient('data_source');

        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
            $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);
            $client->request('PATCH', 'api/rest/v1/product-models', [], [], [], $this->getBody());
        });

        $response = $client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());

        echo PHP_EOL . 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
    }

    private function getProductModelCodes(int $limit)
    {
        $sql = <<<SQL
SELECT product_model.code AS code
FROM pim_catalog_product_model product_model
LIMIT ${limit}
SQL;
        $productModelCodes = [];
        $rows = $this->get('database_connection')->executeQuery($sql)->fetchAll();
        foreach ($rows as $row) {
            $productModelCodes[] = $row['code'];
        }

        return $productModelCodes;
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

        return join("\n", array_map(function ($productCode) use ($categoryCodesAsString) {
            return '{"code": "' . $productCode . '", "categories": ' . $categoryCodesAsString . '}';
        }, $this->getProductModelCodes(self::PRODUCT_MODEL_COUNT)));
    }
}
