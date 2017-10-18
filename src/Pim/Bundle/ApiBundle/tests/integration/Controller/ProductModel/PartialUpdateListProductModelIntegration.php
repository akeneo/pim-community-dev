<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListProductModelIntegration extends AbstractProductModelTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
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

    public function testCreateAndUpdateAListOfProductModels()
    {
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
            ],
        ];

        $this->assertSameProductModels($expectedProductModels['sub_sweat_option_a'], 'sub_sweat_option_a');
        $this->assertSameProductModels($expectedProductModels['sub_sweat_option_b'], 'sub_sweat_option_b');
        $this->assertSameProductModels($expectedProductModels['sweat'], 'sweat');
        $this->assertSameProductModels($expectedProductModels['root_product_model'], 'root_product_model');
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
{"line":1,"code":"sub_sweat_option_a","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Variant axis \"a_simple_select\" cannot be modified, \"Option B\" given"}]}
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
    {"code": "sub_sweat_option_a", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionA"}]}}
    {"code": "sub_sweat_option_b", "parent": "sweat", "values": {"a_simple_select": [{"locale": null, "scope": null, "data": "optionA"}]}}
JSON;

        $expectedContent =
<<<JSON
{"line":1,"code":"sub_sweat_option_a","status_code":204}
{"line":2,"code":"sub_sweat_option_b","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Cannot set value \"Option A\" for the attribute axis \"a_simple_select\", as another sibling entity already has this value"}]}
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
        $standardizedProductModel = $this->get('pim_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($expectedProductModel);
        NormalizedProductCleaner::clean($standardizedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
