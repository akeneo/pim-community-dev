<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CollectApiErrorsCountEndToEnd extends ApiTestCase
{
    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var Connection */
    private $dbalConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');

        $this->dbalConnection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_collects_the_error_count_from_a_product_delete(): void
    {
        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $client->request('DELETE', '/api/rest/v1/products/unknown_product_identifier');
        Assert::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        $this->errorCountMustBe('erp', 1, ErrorTypes::BUSINESS);
    }

    public function test_it_collects_the_error_count_from_a_product_create(): void
    {
        $this->attributeLoader->create([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->familyLoader->create([
            'code' => 'planeswalker',
            'attributes' => ['sku', 'name']
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = <<<JSON
{
    "identifier": "teferi_time_raveler",
    "family": "planeswalker",
    "values": {
        "description": [{
            "locale": null,
            "scope": null,
            "data": "Each opponent can only cast spells any time they could cast a sorcery."
        }]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->errorCountMustBe('erp', 1, ErrorTypes::BUSINESS);
    }

    public function test_it_collects_the_error_count_from_a_product_partial_update()
    {
        $this->attributeLoader->create([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'max_characters' => 5
        ]);
        $this->attributeLoader->create([
            'code' => 'length',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Length',
            'default_metric_unit' => 'CENTIMETER',
            'negative_allowed' => false,
            'decimals_allowed' => false,
        ]);
        $this->familyLoader->create([
            'code' => 'screen',
            'attributes' => ['sku', 'length', 'name']
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = <<<JSON
{
    "identifier": "big_screen",
    "family": "screen",
    "values": {
        "name": [{
            "locale": null,
            "scope": null,
            "data": "This name is too long."
        }],
        "length": [{
            "locale": null,
            "scope": null,
            "data": {
                "amount": 2,
                "unit": "invalid_unit"
            }
        }]
    }
}
JSON;

        $client->request('PATCH', '/api/rest/v1/products/big_screen', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->errorCountMustBe('erp', 2, ErrorTypes::BUSINESS);
    }

    public function test_it_collects_the_error_count_from_a_product_partial_update_list(): void
    {
        $this->attributeLoader->create([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->familyLoader->create([
            'code' => 'shoes',
            'attributes' => ['sku', 'name']
        ]);
        $this->productLoader->create('high-top_sneakers', [
            'family' => 'shoes',
            'values' => [
                'name' => [['data' => 'High-Top Sneakers', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = '';
        // Error: unknown attribute "description"
        $content .= json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'shoes',
            'values' => [
                'description' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => 'High-Top Sneakers with white cotton laces.',
                ]]
            ]
        ]);
        $content .= PHP_EOL;
        // Success
        $content .= json_encode([
            'identifier' => 'high-top_sneakers',
            'values' => [
                'name' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => 'Vintage Sneakers'
                ]]
            ]
        ]);
        $streamedContent = '';
        ob_start(function ($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;
            return '';
        });
        $client->request(
            'PATCH',
            '/api/rest/v1/products',
            [],
            [],
            ['HTTP_content_type' => StreamResourceResponse::CONTENT_TYPE],
            $content
        );
        ob_end_flush();

        Assert::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->errorCountMustBe('erp', 1, ErrorTypes::BUSINESS);
    }

    private function errorCountMustBe(string $connectionCode, int $count, string $errorType): void
    {
        $selectQuery = <<<SQL
SELECT count(connection_code) AS count
FROM akeneo_connectivity_connection_audit_error
WHERE connection_code = :code AND error_count = :count AND error_type = :type
SQL;

        $result = $this->dbalConnection->executeQuery(
            $selectQuery,
            [
                'code' => $connectionCode,
                'count' => $count,
                'type' => $errorType,
            ],
            [
                'code' => Types::STRING,
                'count' => Types::INTEGER,
                'type' => Types::STRING,
            ]
        )->fetchAll(FetchMode::COLUMN);

        Assert::assertCount(1, $result);
        Assert::assertEquals('1', $result[0]);
    }
}
