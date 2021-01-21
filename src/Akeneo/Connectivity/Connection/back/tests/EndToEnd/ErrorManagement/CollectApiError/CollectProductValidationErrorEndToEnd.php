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
class CollectProductValidationErrorEndToEnd extends ApiTestCase
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

    public function test_it_collects_the_attribute_option_does_not_exist_validation_error(): void
    {
        $this->attributeLoader->create([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect'
        ]);
        $this->familyLoader->create([
            'code' => 'shoes',
            'attributes' => ['sku', 'color']
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
                'color' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'unknown_color',
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
            'property' => 'values',
            'message' => 'The unknown_color value is not in the color attribute option list.',
            'attribute' => 'color',
            'locale' => null,
            'scope' => null,
            'type' => 'violation_error',
            'message_template' => 'The %invalid_option% value is not in the %attribute_code% attribute option list.',
            'message_parameters' => [
                '%attribute_code%' => 'color',
                '%invalid_option%' => 'unknown_color',
            ],
            'documentation' => [
                [
                    'message' => 'More information about select attributes: {manage_attributes_options}.',
                    'parameters' => [
                        'manage_attributes_options' => [
                            'type' => 'href',
                            'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#manage-simple-and-multi-selects-attribute-options',
                            'title' => 'Manage select attributes options'
                        ]
                    ],
                    'style' => 'information'
                ],
                [
                    'message' => 'Please check the {attribute_options_settings}.',
                    'parameters' => [
                        'attribute_options_settings' => [
                            'type' => 'route',
                            'route' => 'pim_enrich_attribute_edit',
                            'routeParameters' => [
                                'code' => 'color'
                            ],
                            'title' => 'Options settings of the color attribute'
                        ]
                    ],
                    'style' => 'text'
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
}
