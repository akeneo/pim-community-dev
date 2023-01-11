<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\UpdateCatalogAction
 */
class GetCatalogErrorsActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsCatalogProductSelectionCriteriaErrors(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'color',
                    'operator' => Operator::IN_LIST,
                    'value' => ['blue'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => [],
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/errors',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(1, $payload);
        Assert::assertEquals('productSelectionCriteria[0][value]', $payload[0]['propertyPath']);
    }

    public function testItReturnsCatalogProductFilterValuesErrors(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: [
                'channels' => [
                    'br_BR',
                ],
            ],
        );

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/errors',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(1, $payload);
        Assert::assertEquals('productValueFilters[channels][0]', $payload[0]['propertyPath']);
    }

    public function testItReturnsCatalogProductMappingErrors(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            productMappingSchema: $this->getValidSchemaData(),
            catalogProductMapping: [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => 'ecommerce',
                    'locale' => null,
                ],
            ],
        );

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/errors',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertCount(1, $payload);
        Assert::assertEquals('productMapping[uuid][scope]', $payload[0]['propertyPath']);
    }

    public function testItReturnsNoCatalogErrors(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('shopifi');

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductSelection: [
                [
                    'field' => 'color',
                    'operator' => Operator::IN_LIST,
                    'value' => ['blue'],
                    'scope' => null,
                    'locale' => null,
                ],
            ],
            catalogProductValueFilters: [
                'locales' => [
                    'en_US',
                ],
            ],
            productMappingSchema: $this->getValidSchemaData(),
            catalogProductMapping: [
                'uuid' => [
                    'source' => 'uuid',
                    'scope' => null,
                    'locale' => null,
                ],
            ],
        );

        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['blue', 'green'],
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/errors',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals([], $payload);
    }

    private function getValidSchemaData(): string
    {
        return <<<'JSON_WRAP'
        {
          "$id": "https://example.com/product",
          "$schema": "https://api.akeneo.com/mapping/product/0.0.2/schema",
          "$comment": "My first schema !",
          "title": "Product Mapping",
          "description": "JSON Schema describing the structure of products expected by our application",
          "type": "object",
          "properties": {
            "uuid": {
              "type": "string"
            }
          }
        }
        JSON_WRAP;
    }
}
