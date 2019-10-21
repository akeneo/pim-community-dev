<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListProductModelEndToEnd extends AbstractProductModelTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->createProductModel(
            [
                'code' => 'sub_sweat_option_a',
                'parent' => 'sweat',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'optionA',
                        ],
                    ],
                ]
            ]
        );

        $this->createVariantProduct('apollon_optiona_true', [
            'categories' => ['master'],
            'parent' => 'sub_sweat_option_a',
            'values' => [
                'a_yes_no' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @group critical
     */
    public function testCreateAndUpdateAListOfProductModels()
    {
        // We remove all completenesses in order to check that completeness is recomputed.
        $this->get('database_connection')->exec('TRUNCATE pim_catalog_completeness;');

        $data =
            <<<JSON
    {"code": "sub_sweat_option_a", "family_variant": "familyVariantA1", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionA"}]}}
    {"code": "root_product_model", "family_variant": "familyVariantA1", "values": {"a_number_float": [{"locale": null, "scope": null, "data": "13"}]}}
    {"code": "sweat", "values": {"a_number_float": [{"locale": null, "scope": null, "data": "10.5000"}]}}
    {"code": "sub_sweat_option_b", "family_variant": "familyVariantA1", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"sub_sweat_option_a","status_code":204}
{"line":2,"code":"root_product_model","status_code":201}
{"line":3,"code":"sweat","status_code":204}
{"line":4,"code":"sub_sweat_option_b","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedProductModels = [
            'sub_sweat_option_a' => [
                'code'           => 'sub_sweat_option_a',
                'family_variant' => "familyVariantA1",
                'parent'         => "sweat",
                'categories'     => [],
                'values'         => [
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                ['amount' => '50.00', 'currency' => 'EUR'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => 'ecommerce',
                            'data'   => 'I like sweat!',
                        ],
                    ],
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'optionA',
                        ],
                    ],
                    'a_number_float' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '10.5000',
                        ],
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations' => [],
            ],
            'sweat' => [
                'code'           => 'sweat',
                'family_variant' => "familyVariantA1",
                'parent'         => null,
                'categories'     => [],
                'values'         => [
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                ['amount' => '50.00', 'currency' => 'EUR'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => 'ecommerce',
                            'data'   => 'I like sweat!',
                        ],
                    ],
                    'a_number_float' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '10.5000',
                        ],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [],
            ],
            'sub_sweat_option_b' => [
                'code'           => 'sub_sweat_option_b',
                'family_variant' => "familyVariantA1",
                'parent'        => "sweat",
                'categories'    => [],
                'values'        => [
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => [
                                ['amount' => '50.00', 'currency' => 'EUR'],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => 'ecommerce',
                            'data'   => 'I like sweat!',
                        ],
                    ],
                    'a_number_float' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '10.5000',
                        ],
                    ],
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'optionB',
                        ],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [],
            ],
            'root_product_model' => [
                'code'           => 'root_product_model',
                'family_variant' => "familyVariantA1",
                'parent'        => null,
                'categories'    => [],
                'values'        => [
                    'a_number_float' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '13.0000',
                        ],
                    ],
                ],
                'created' => '2016-06-14T13:12:50+02:00',
                'updated' => '2016-06-14T13:12:50+02:00',
                'associations' => [],
            ],
        ];

        $this->assertSameProductModels($expectedProductModels['sub_sweat_option_a'], 'sub_sweat_option_a');
        $this->assertSameProductModels($expectedProductModels['sub_sweat_option_b'], 'sub_sweat_option_b');
        $this->assertSameProductModels($expectedProductModels['sweat'], 'sweat');
        $this->assertSameProductModels($expectedProductModels['root_product_model'], 'root_product_model');

        $this->assertCompletenessWasComputedForProducts(['apollon_optiona_true']);

        $esProduct = $this->getProductFromIndex('apollon_optiona_true');
        Assert::assertNotNull($esProduct);
        Assert::assertArrayHasKey('a_yes_no-boolean', $esProduct['values']);
        Assert::assertArrayHasKey('a_price-prices', $esProduct['values']);
        Assert::assertArrayHasKey('a_simple_select-option', $esProduct['values']);

        $esProductModel = $this->getProductModelFromIndex('sub_sweat_option_a');
        Assert::assertNotNull($esProductModel);
    }

    public function testCreateAndUpdateSameProductModel()
    {
        $data =
            <<<JSON
    {"code": "sub_sweat_option_b", "family_variant": "familyVariantA1", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
    {"code": "sub_sweat_option_b", "family_variant": "familyVariantA1", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"sub_sweat_option_b","status_code":201}
{"line":2,"code":"sub_sweat_option_b","status_code":204}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateProductModelWithUpdatedAxeValue()
    {
        $data =
            <<<JSON
    {"code": "sub_sweat_option_a", "family_variant": "familyVariantA1", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"sub_sweat_option_a","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Variant axis \"a_simple_select\" cannot be modified, \"[optionB]\" given"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testCreateAndUpdateProductModelsWithAlreadyExistingAxeValue()
    {
        $data =
            <<<JSON
    {"code": "sub_sweat_option_a", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionA"}]}}
    {"code": "sub_sweat_option_b", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionA"}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"code":"sub_sweat_option_a","status_code":204}
{"line":2,"code":"sub_sweat_option_b","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Cannot set value \"[optionA]\" for the attribute axis \"a_simple_select\" on product model \"sub_sweat_option_b\", as the product model \"sub_sweat_option_a\" already has this value"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testCreateAndUpdateProductModelsWithUpdatedAxeValue()
    {
        $data =
            <<<JSON
    {"code": "sub_sweat_option_a", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
    {"code": "sub_sweat_option_b", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionB"}]}}
JSON;
        $expectedContent =
            <<<JSON
{"line":1,"code":"sub_sweat_option_a","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Variant axis \"a_simple_select\" cannot be modified, \"[optionB]\" given"}]}
{"line":2,"code":"sub_sweat_option_b","status_code":201}
JSON;
        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testErrorWhenCodeIsMissing()
    {
        $data =
            <<<JSON
    {"identifier": "my_code"}
    {"code": null}
    {"code": ""}
    {"code": " "}
    {}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"status_code":422,"message":"Code is missing."}
{"line":2,"status_code":422,"message":"Code is missing."}
{"line":3,"status_code":422,"message":"Code is missing."}
{"line":4,"status_code":422,"message":"Code is missing."}
{"line":5,"status_code":422,"message":"Code is missing."}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/product-models', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * @param array  $expectedProductModel normalized data of the product model that should be created
     * @param string $code                 code of the product model that should be created
     */
    protected function assertSameProductModels(array $expectedProductModel, $code)
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code);
        $standardizedProductModel = $this->get('pim_standard_format_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($expectedProductModel);
        NormalizedProductCleaner::clean($standardizedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    protected function getProductFromIndex(string $identifier): ?array
    {
        $esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $esProductClient->refreshIndex();
        $res = $esProductClient->search(['query' => ['term' => ['identifier' => $identifier]]]);

        return $res['hits']['hits'][0]['_source'] ?? null;
    }

    protected function getProductModelFromIndex(string $code): ?array
    {
        $esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $esProductClient->refreshIndex();
        $res = $esProductClient->search(['query' => ['term' => ['identifier' => $code]]]);

        return $res['hits']['hits'][0]['_source'] ?? null;
    }

    private function assertCompletenessWasComputedForProducts(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
            Assert::assertNotNull($product);

            $completenesses = $this
                ->get('akeneo.pim.enrichment.product.query.get_product_completenesses')
                ->fromProductId($product->getId());
            Assert::assertCount(6, $completenesses); // 3 channels * 2 locales
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
