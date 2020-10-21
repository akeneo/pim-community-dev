<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\VariantProduct\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PartialUpdateVariantToSimpleEndToEnd extends AbstractProductTestCase
{
    /**
     * @test
     */
    public function it_converts_a_variant_product_to_a_simple_product()
    {
        $expectedProduct = [
            'identifier' => 'product_family_variant_yes',
            'family' => "familyA",
            'parent' => null,
            'groups' => [],
            'categories' => ['master', 'categoryA2'],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_family_variant_yes'],
                ],
                'a_price' => [
                    'data' => [
                        'data' => [['amount' => '50.00', 'currency' => 'EUR']],
                        'locale' => null,
                        'scope' => null
                    ],
                ],
                'a_number_float' => [['data' => '12.5000', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    [
                        'data' => 'my pink tshirt',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce'
                    ]
                ],
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];


        $client = $this->createAuthenticatedClient();
        $data = '{"identifier": "product_family_variant_yes", "parent": null}';
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant_yes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_family_variant_yes');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_family_variant_yes',
            $response->headers->get('location')
        );
    }

    /**
     * @test
     */
    function it_converts_a_variant_to_simple_and_updates_values()
    {
        $expectedProduct = [
            'identifier' => 'product_family_variant_yes',
            'family' => "familyA",
            'parent' => null,
            'groups' => [],
            'categories' => ['categoryA1', 'categoryA2'],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_family_variant_yes'],
                ],
                'a_number_float' => [['data' => '12.5000', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    [
                        'data' => 'my pink tshirt',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce'
                    ]
                ],
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $client = $this->createAuthenticatedClient();
        $data = <<<JSON
    {
        "identifier": "product_family_variant_yes",
        "parent": null,
        "categories": ["categoryA1", "categoryA2"],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": false
            }
          ],
          "a_price": [
            {
              "data": [],
              "locale": null,
              "scope": null
            }
          ]
        }
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant_yes', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_family_variant_yes');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_family_variant_yes',
            $response->headers->get('location')
        );
    }

    /**
     * @test
     */
    public function it_converts_several_variant_products()
    {
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_family_variant_yes","status_code":204}
{"line":2,"identifier":"product_family_variant_no","status_code":204}
JSON;

        $data = <<<JSON
{"identifier": "product_family_variant_yes", "parent": null}
{"identifier": "product_family_variant_no", "parent": null}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createProductModel(
            [
                'code' => 'root',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_price' => [
                        'data' => [
                            'data' => [['amount' => '50', 'currency' => 'EUR']],
                            'locale' => null,
                            'scope' => null
                        ],
                    ],
                    'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'data' => 'my pink tshirt',
                            'locale' => 'en_US',
                            'scope' => 'ecommerce'
                        ]
                    ],
                ]
            ]
        );
        $this->createProductModel(
            [
                'code' => 'sub',
                'parent' => 'root',
                'categories' => ['master'],
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']]
                ],
            ]
        );
        $this->createProduct(
            'product_family_variant_yes',
            [
                'family' => 'familyA',
                'parent' => 'sub',
                'categories' => ['categoryA2'],
                'values' => [
                    'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
                ]
            ]
        );

        $this->createProduct(
            'product_family_variant_no',
            [
                'family' => 'familyA',
                'parent' => 'sub',
                'categories' => ['categoryA2'],
                'values' => [
                    'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]
                ]
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('doctrine.orm.default_entity_manager')->clear();
    }
}
