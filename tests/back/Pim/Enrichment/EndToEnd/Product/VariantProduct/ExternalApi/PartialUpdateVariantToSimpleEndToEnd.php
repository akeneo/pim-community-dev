<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\VariantProduct\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PartialUpdateVariantToSimpleEndToEnd extends AbstractProductTestCase
{
    /** @var int */
    private $rootProductModelId;

    /** @var int */
    private $subProductModelId;

    /**
     * @test
     */
    public function it_converts_a_variant_product_to_a_simple_product_and_reindexes_its_former_ancestors()
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
                        'data' => [
                            ['amount' => '400.00', 'currency' => 'CNY'],
                            ['amount' => '50.00', 'currency' => 'EUR'],
                            ['amount' => '60.00', 'currency' => 'USD'],
                        ],
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
                'a_number_float' => [['data' => '12.5000', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    [
                        'data' => 'my pink tshirt',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'X_SELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
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

        // the incomplete product for ecommerce was detached, only the complete one remains
        $expectedAllComplete = [
            'ecommerce' => [
                'en_US' => 1,
            ],
            'ecommerce_china' => [
                'en_US' => 1,
                'zh_CN' => 1,
            ],
        ];
        $expectedAllIncomplete = [
            'ecommerce' => [
                'en_US' => 0,
            ],
            'ecommerce_china' => [
                'en_US' => 0,
                'zh_CN' => 0,
            ],
        ];
        $this->assertProductModelCompleteness($this->rootProductModelId, $expectedAllComplete, $expectedAllIncomplete);
        $this->assertProductModelCompleteness($this->subProductModelId, $expectedAllComplete, $expectedAllIncomplete);
    }

    /**
     * @test
     */
    function it_converts_a_variant_to_simple_and_updates_values()
    {
        $expectedProduct = [
            'identifier' => 'product_family_variant_no',
            'family' => "familyA",
            'parent' => null,
            'groups' => [],
            'categories' => ['categoryA1', 'categoryA2'],
            'enabled' => true,
            'values' => [
                'sku' => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_family_variant_no'],
                ],
                'a_number_float' => [['data' => '12.5000', 'locale' => null, 'scope' => null]],
                'a_localized_and_scopable_text_area' => [
                    [
                        'data' => 'my pink tshirt',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ],
                ],
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                'a_text_area' => [['locale' => null, 'scope' => null, 'data' => 'Lorem ipsum dolor sit amet']],
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [
                'PACK' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'UPSELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
                'X_SELL' => [
                    'products' => [],
                    'product_models' => [],
                    'groups' => [],
                ],
            ],
            'quantified_associations' => [],
        ];

        $client = $this->createAuthenticatedClient();
        $data = <<<JSON
    {
        "identifier": "product_family_variant_no",
        "parent": null,
        "categories": ["categoryA1", "categoryA2"],
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
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
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant_no', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'product_family_variant_no');
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_family_variant_no',
            $response->headers->get('location')
        );

        // the complete product for ecommerce was detached, only the incomplete one remains
        $expectedAllComplete = [
            'ecommerce' => [
                'en_US' => 0,
            ],
            'ecommerce_china' => [
                'en_US' => 1,
                'zh_CN' => 1,
            ],
        ];
        $expectedAllIncomplete = [
            'ecommerce' => [
                'en_US' => 1,
            ],
            'ecommerce_china' => [
                'en_US' => 0,
                'zh_CN' => 0,
            ],
        ];
        $this->assertProductModelCompleteness($this->rootProductModelId, $expectedAllComplete, $expectedAllIncomplete);
        $this->assertProductModelCompleteness($this->subProductModelId, $expectedAllComplete, $expectedAllIncomplete);
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

        // all products were detached, there are no more complete nor incomplete products for either channel
        $expectedAllComplete = $expectedAllIncomplete = [];
        $this->assertProductModelCompleteness($this->rootProductModelId, $expectedAllComplete, $expectedAllIncomplete);
        $this->assertProductModelCompleteness($this->subProductModelId, $expectedAllComplete, $expectedAllIncomplete);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $tablet = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('tablet');
        $this->get('pim_catalog.remover.channel')->remove($tablet);

        $familyA = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $this->get('pim_catalog.updater.family')->update(
            $familyA,
            [
                'attribute_requirements' => [
                    'ecommerce' => [
                        'sku',
                        'a_price',
                        'a_number_float',
                        'a_localized_and_scopable_text_area',
                        'a_simple_select',
                        'a_yes_no',
                        'a_text_area',
                    ],
                ],
            ]
        );
        $this->get('pim_catalog.saver.family')->save($familyA);

        $root = $this->createProductModel(
            [
                'code' => 'root',
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_price' => [
                        'data' => [
                            'data' => [
                                ['amount' => '400', 'currency' => 'CNY'],
                                ['amount' => '50', 'currency' => 'EUR'],
                                ['amount' => '60', 'currency' => 'USD'],
                            ],
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'data' => 'my pink tshirt',
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                        ],
                    ],
                ],
            ]
        );
        $this->rootProductModelId = $root->getId();

        $sub = $this->createProductModel(
            [
                'code' => 'sub',
                'parent' => 'root',
                'categories' => ['master'],
                'family_variant' => 'familyVariantA1',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                ],
            ]
        );
        $this->subProductModelId = $sub->getId();

        $this->createProduct(
            'product_family_variant_yes',
            [
                'family' => 'familyA',
                'parent' => 'sub',
                'categories' => ['categoryA2'],
                'values' => [
                    'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                ],
            ]
        );
        $this->createProduct(
            'product_family_variant_no',
            [
                'family' => 'familyA',
                'parent' => 'sub',
                'categories' => ['categoryA2'],
                'values' => [
                    'a_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
                    'a_text_area' => [['data' => 'Lorem ipsum dolor sit amet', 'locale' => null, 'scope' => null]],
                ],
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('doctrine.orm.default_entity_manager')->clear();

        // at this stage, there is one complete product and one incomplete product for the ecommerce channel,
        // whereas the 2 products are complete for the ecommerce_china channel
        $initialAllComplete = [
            'ecommerce' => [
                'en_US' => 0,
            ],
            'ecommerce_china' => [
                'en_US' => 1,
                'zh_CN' => 1,
            ],
        ];
        $initialAllIncomplete = [
            'ecommerce' => [
                'en_US' => 0,
            ],
            'ecommerce_china' => [
                'en_US' => 0,
                'zh_CN' => 0,
            ],
        ];
        $this->assertProductModelCompleteness($this->rootProductModelId, $initialAllComplete, $initialAllIncomplete);
        $this->assertProductModelCompleteness($this->subProductModelId, $initialAllComplete, $initialAllIncomplete);
    }

    private function assertProductModelCompleteness(
        int $productModelId,
        array $expectedAllComplete,
        array $expectedAllIncomplete
    ): void {
        $indexedProductModel = $this->get('akeneo_elasticsearch.client.product_and_product_model')
                                    ->get(sprintf('product_model_%d', $productModelId));

        Assert::assertEqualsCanonicalizing($expectedAllComplete, $indexedProductModel['_source']['all_complete']);
        Assert::assertEqualsCanonicalizing($expectedAllIncomplete, $indexedProductModel['_source']['all_incomplete']);
    }
}
