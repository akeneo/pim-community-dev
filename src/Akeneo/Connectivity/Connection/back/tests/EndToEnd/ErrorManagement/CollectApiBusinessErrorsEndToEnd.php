<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectApiBusinessErrorsEndToEnd extends ApiTestCase
{
    /** @var Client */
    private $esClient;

    public function test_it_does_not_collect_an_error_from_a_not_found_http_exception(): void
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

        $this->esClient->refreshIndex();
        $result = $this->esClient->search([]);

        Assert::assertCount(0, $result['hits']['hits']);
    }

    public function test_it_collects_an_error_from_a_unprocessable_entity_http_exception(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createFamily([
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
    "values": {
        "description": [{
            "locale": null,
            "scope": null,
            "data": "Each opponent can only cast spells any time they could cast a sorcery."
        }]
    }
}
JSON;

        $expectedContent = [
            'type' => 'domain_error',
            'domain_error_code' => '1',
            'message' => 'Attribute "description" does not exist.',
            'documentation' =>  [
                [
                    'message' => 'More information about attributes: %s %s.',
                    'params' =>  [
                        [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html',
                            'title' => 'What is an attribute?',
                            'type' => 'href',
                        ],
                        [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                            'title' => 'Manage your attributes',
                            'type' => 'href',
                        ]
                    ]
                ],
                [
                    'message' => 'Please check your %s.',
                    'params' => [
                        [
                            'route' => 'pim_enrich_attribute_index',
                            'params' => [],
                            'title' => 'Attributes settings',
                            'type' => 'route',
                        ]
                    ]
                ]
            ],
            'product' =>  [
                'id' => null,
                'identifier' => null
            ]
        ];

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->esClient->refreshIndex();
        $result = $this->esClient->search([]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_each_violation_error_from_a_violation_http_exception()
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'max_characters' => 5
        ]);
        $this->createAttribute([
            'code' => 'length',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Length',
            'default_metric_unit' => 'CENTIMETER',
            'negative_allowed' => false,
            'decimals_allowed' => false,
        ]);
        $this->createFamily([
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

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'values',
                    'message' => 'This value is too long. It should have 5 characters or less.',
                    'attribute' => 'name',
                    'locale' => null,
                    'scope' => null,
                    'type' => 'violation_error',
                    'message_template' => 'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
                    'message_parameters' => [
                        '{{ value }}' => '"This name is too long."',
                        '{{ limit }}' => 5
                    ],
                    'product' => [
                        'id' => null,
                        'identifier' => 'big_screen',
                    ]
                ],
                [
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
                        'identifier' => 'big_screen'
                    ]
                ],
            ]
        ];

        $this->esClient->refreshIndex();
        $result = $this->esClient->search([]);

        Assert::assertCount(2, $result['hits']['hits']);

        $doc1 = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc1['connection_code']);
        Assert::assertEquals($expectedContent['errors'][0], $doc1['content']);

        $doc2 = $result['hits']['hits'][1]['_source'];
        Assert::assertEquals('erp', $doc2['connection_code']);
        Assert::assertEquals($expectedContent['errors'][1], $doc2['content']);
    }

    public function test_it_collects_errors_from_a_product_partial_update_list(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createFamily([
            'code' => 'shoes',
            'attributes' => ['sku', 'name']
        ]);
        $this->createProduct('high-top_sneakers', [
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

        $expectedContent = [
            'type' => 'domain_error',
            'domain_error_code' => '1',
            'message' => 'Attribute "description" does not exist.',
            'documentation' => [
                [
                    'message' => 'More information about attributes: %s %s.',
                    'params' => [
                        [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html',
                            'title' => 'What is an attribute?',
                            'type' => 'href'
                        ],
                        [
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                            'title' => 'Manage your attributes',
                            'type' => 'href'
                        ]
                    ]
                ],
                [
                    'message' => 'Please check your %s.',
                    'params' => [
                        [
                            'route' => 'pim_enrich_attribute_index',
                            'params' => [],
                            'title' => 'Attributes settings',
                            'type' => 'route'
                        ]
                    ]
                ]
            ],
            'product' => [
                'id' => 1,
                'identifier' => 'high-top_sneakers'
            ]
        ];

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

        $this->esClient->refreshIndex();
        $result = $this->esClient->search([]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);
        Assert::assertSame($expectedContent, $doc['content']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_connectivity.client.connection_error');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(array $data): void
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints, 'The validation from the attribute creation failed.');
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        $this->assertCount(0, $constraints, 'The validation from the family creation failed.');
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createProduct($identifier, array $data): ProductInterface
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family);
        $this->updateProduct($product, $data);

        return $product;
    }

    private function updateProduct(ProductInterface $product, array $data): void
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraints = $this->get('validator')->validate($product);
        $this->assertCount(0, $constraints, 'The validation from the product creation failed.');
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
