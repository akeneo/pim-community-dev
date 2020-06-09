<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Elasticsearch\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectProductDomainErrorEndToEnd extends ApiTestCase
{
    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var Client */
    private $elasticsearch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');

        $this->elasticsearch = $this->get('akeneo_connectivity.client.connection_error');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_collects_validation_errors(): void
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
            'code' => 'shoes',
            'attributes' => ['sku', 'name', 'length']
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

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'shoes',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'this_value_is_too_long',
                    ]
                ],
                'length' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            'amount' => 2,
                            'unit' => 'this_is_invalid_unit_type'
                        ],
                    ]
                ]
            ]
        ]);

        $client->request('PATCH', '/api/rest/v1/products/high-top_sneakers', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(2, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);

        $expectedContent = [
            'property' => 'values',
            'message' => 'This value is too long. It should have 5 characters or less.',
            'attribute' => 'name',
            'locale' => null,
            'scope' => null,
            'type' => 'violation_error',
            'message_template' => 'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
            'message_parameters' => [
                '{{ value }}' => '"this_value_is_too_long"',
                '{{ limit }}' => 5
            ],
            'product' => [
                'id' => null,
                'identifier' => 'high-top_sneakers'
            ]
        ];
        Assert::assertEquals($expectedContent, $doc['content']);

        $doc = $result['hits']['hits'][1]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);

        $expectedContent = [
            'property' => 'values',
            'message' => 'Please specify a valid metric unit',
            'attribute' => 'length',
            'locale' => null,
            'scope' => null,
            'type' => 'violation_error',
            'message_template' => 'Please specify a valid metric unit',
            'message_parameters' => [],
            'product' => [
                'id' => null,
                'identifier' => 'high-top_sneakers'
            ]
        ];
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_the_unknown_family_error(): void
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

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'unknown_family_code',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'High-Top Sneakers',
                    ]
                ]
            ]
        ]);

        $client->request('PATCH', '/api/rest/v1/products/high-top_sneakers', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);

        $expectedContent = [
            'type' => 'domain_error',
            'message' => 'Property "family" expects a valid family code. The family does not exist, "unknown_family_code" given.',
            'product' => [
                'id' => null,
                'identifier' => 'high-top_sneakers'
            ]
        ];
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_an_unknown_attribute_error(): void
    {
        $this->familyLoader->create(['code' => 'shoes', 'attributes' => ['sku']]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'shoes',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'High-Top Sneakers',
                    ]
                ]
            ]
        ]);

        $expectedContent = [
            'type' => 'domain_error',
            'message' => 'Attribute "name" does not exist.',
            'documentation' =>  [
                [
                    'message' => 'More information about attributes: {what_is_attribute} {manage_attribute}.',
                    'parameters' =>  [
                        'what_is_attribute' => [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html',
                            'title' => 'What is an attribute?',
                            'type' => 'href',
                        ],
                        'manage_attribute' => [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                            'title' => 'Manage your attributes',
                            'type' => 'href',
                        ]
                    ]
                ],
                [
                    'message' => 'Please check your {attribute_settings}.',
                    'parameters' => [
                        'attribute_settings' => [
                            'route' => 'pim_enrich_attribute_index',
                            'routeParameters' => [],
                            'title' => 'Attributes settings',
                            'type' => 'route',
                        ]
                    ]
                ]
            ],
            'product' =>  [
                'id' => null,
                'identifier' => 'high-top_sneakers'
            ]
        ];

        $client->request('PATCH', '/api/rest/v1/products/high-top_sneakers', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);
        Assert::assertEquals($expectedContent, $doc['content']);
    }
}
