<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyLoader;
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

    public function test_it_collects_an_unknown_family_error(): void
    {
        $this->attributeLoader->create([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);

        $this->familyLoader->create([
            'code' => 'shoes',
            'attributes' => ['sku', 'name']
        ]);

        $this->productLoader->create('high-top_sneakers', ['family' => 'shoes']);

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
            'message' => 'The unknown_family_code family does not exist in your PIM.',
            'message_template' => 'The {family_code} family does not exist in your PIM.',
            'message_parameters' => ['family_code' => 'unknown_family_code'],
            'documentation' =>  [
                [
                    'message' => 'Please check your {family_settings}.',
                    'parameters' => [
                        'family_settings' => [
                            'type' => 'route',
                            'route' => 'pim_enrich_family_index',
                            'routeParameters' => [],
                            'title' => 'Family settings',
                        ],
                    ],
                    'style' => 'text'
                ],
                [
                    'message' => 'More information about families: {what_is_a_family} {manage_your_families}.',
                    'parameters' => [
                        'what_is_a_family' => [
                            'type' => 'href',
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-a-family.html',
                            'title' => 'What is a family?',
                        ],
                        'manage_your_families' => [
                            'type' => 'href',
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-families.html',
                            'title' => 'Manage your families',
                        ],
                    ],
                    'style' => 'information'
                ]
            ],
            'product' => [
                'id' => 1,
                'identifier' => 'high-top_sneakers',
                'label' => 'high-top_sneakers',
                'family' => 'shoes'
            ]
        ];
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_an_unknown_attribute_error(): void
    {
        $this->familyLoader->create(['code' => 'shoes', 'attributes' => ['sku']]);
        $this->productLoader->create('high-top_sneakers', ['family' => 'shoes']);

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
            'message' => 'The name attribute does not exist in your PIM.',
            'message_template' => 'The {attribute_code} attribute does not exist in your PIM.',
            'message_parameters' => ['attribute_code' => 'name'],
            'documentation' =>  [
                [
                    'message' => 'Please check your {attribute_settings}.',
                    'parameters' => [
                        'attribute_settings' => [
                            'route' => 'pim_enrich_attribute_index',
                            'routeParameters' => [],
                            'title' => 'Attributes settings',
                            'type' => 'route',
                        ]
                    ],
                    'style' => 'text'
                ],
                [
                    'message' => 'More information about attributes: {what_is_attribute} {manage_attribute}.',
                    'parameters' => [
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
                    ],
                    'style' => 'information'
                ]
            ],
            'product' => [
                'id' => 1,
                'identifier' => 'high-top_sneakers',
                'label' => 'high-top_sneakers',
                'family' => 'shoes'
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

    public function test_it_collects_an_unknown_category_error(): void
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
            'categories' => ['unknown_category_code']
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
            'message' => 'The unknown_category_code category does not exist in your PIM.',
            'message_template' => 'The {category_code} category does not exist in your PIM.',
            'message_parameters' => ['category_code' => 'unknown_category_code'],
            'documentation' => [
                [
                    'message' => 'Please check your {categories_settings}.',
                    'parameters' => [
                        'categories_settings' => [
                            'type' => 'route',
                            'route' => 'pim_enrich_categorytree_index',
                            'routeParameters' => [],
                            'title' => 'Categories settings',
                        ],
                    ],
                    'style' => 'text'
                ],
                [
                    'message' => 'More information about catalogs and categories: {what_is_a_category} {categorize_a_product}.',
                    'parameters' => [
                        'what_is_a_category' => [
                            'type' => 'href',
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html',
                            'title' => 'What is a category?',
                        ],
                        'categorize_a_product' => [
                            'type' => 'href',
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/categorize-a-product.html',
                            'title' => 'Categorize a product',
                        ],
                    ],
                    'style' => 'information'
                ]
            ],
            'product' => [
                'id' => null,
                'identifier' => 'high-top_sneakers',
                'label' => 'high-top_sneakers',
                'family' => 'shoes'
            ]
        ];
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_an_unknown_product_error(): void
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

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);

        $expectedContent = [
            'type' => 'domain_error',
            'message' => 'The unknown_product_identifier product does not exist in your PIM or you do not have permission to access it.',
            'message_template' => 'The {product_identifier} product does not exist in your PIM or you do not have permission to access it.',
            'message_parameters' => ['product_identifier' => 'unknown_product_identifier']
        ];
        Assert::assertEquals($expectedContent, $doc['content']);
    }
}
