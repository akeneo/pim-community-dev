<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateListVariantProductEndToEnd extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->createProductModel(
            [
                'code' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'amor',
                'parent' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );

        // no locale, no scope, 1 category
        $this->createVariantProduct('apollon_optionb_true', [
            'categories' => ['master'],
            'parent' => 'amor',
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
    public function testCreateAndUpdateAListOfProductsVariant()
    {
        $data =
            <<<JSON
    {"identifier": "apollon_optionb_true", "family": "familyA", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": true}]}}
    {"identifier": "apollon_optionb_false", "family": "familyA", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": false}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"identifier":"apollon_optionb_true","status_code":204}
{"line":2,"identifier":"apollon_optionb_false","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
        $this->assertArrayHasKey('content-type', $httpResponse->headers->all());
        $this->assertSame(StreamResourceResponse::CONTENT_TYPE, $httpResponse->headers->get('content-type'));

        $expectedProducts = [
            'apollon_optionb_true' => [
                'identifier'    => 'apollon_optionb_true',
                'family'        => "familyA",
                'parent'        => "amor",
                'groups'        => [],
                'categories'    => ["master"],
                'enabled'       => true,
                'values'        => [
                    'sku'                                => [
                        ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_true'],
                    ],
                    'a_simple_select'                    => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                    "a_price"                            => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => [
                                [
                                    "amount"   => "50.00",
                                    "currency" => "EUR",
                                ],
                            ],
                        ],
                    ],
                    "a_localized_and_scopable_text_area" => [
                        [
                            "locale" => "en_US",
                            "scope"  => "ecommerce",
                            "data"   => "my pink tshirt",
                        ],
                    ],
                    "a_number_float"                     => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => "12.5000",
                        ],
                    ],
                    "a_yes_no"                           => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => true,
                        ],
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations'  => [],
            ],
            'apollon_optionb_false' => [
                'identifier'    => 'apollon_optionb_false',
                'family'        => "familyA",
                'parent'        => "amor",
                'groups'        => [],
                'categories'    => [],
                'enabled'       => true,
                'values'        => [
                    'sku'                                => [
                        ['locale' => null, 'scope' => null, 'data' => 'apollon_optionb_false'],
                    ],
                    'a_simple_select'                    => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                    "a_price"                            => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => [
                                [
                                    "amount"   => "50.00",
                                    "currency" => "EUR",
                                ],
                            ],
                        ],
                    ],
                    "a_localized_and_scopable_text_area" => [
                        [
                            "locale" => "en_US",
                            "scope"  => "ecommerce",
                            "data"   => "my pink tshirt",
                        ],
                    ],
                    "a_number_float"                     => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => "12.5000",
                        ],
                    ],
                    "a_yes_no"                           => [
                        [
                            "locale" => null,
                            "scope"  => null,
                            "data"   => false,
                        ],
                    ],
                ],
                'created'       => '2016-06-14T13:12:50+02:00',
                'updated'       => '2016-06-14T13:12:50+02:00',
                'associations'  => [],
            ],
        ];

        $this->assertSameProducts($expectedProducts['apollon_optionb_true'], 'apollon_optionb_true');
        $this->assertSameProducts($expectedProducts['apollon_optionb_false'], 'apollon_optionb_false');
    }

    public function testCreateAndUpdateSameProductVariant()
    {
        $data =
            <<<JSON
    {"identifier": "apollon_optionb_false", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": false}]}}
    {"identifier": "apollon_optionb_false", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": false}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"identifier":"apollon_optionb_false","status_code":201}
{"line":2,"identifier":"apollon_optionb_false","status_code":204}
JSON;


        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testCreateAndUpdateSameProductVariantWithUpdatedAxeValue()
    {
        $data =
            <<<JSON
    {"identifier": "apollon_optionb_true", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": false}]}}
    {"identifier": "apollon_optionb_false", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": false}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"identifier":"apollon_optionb_true","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Variant axis \"a_yes_no\" cannot be modified, \"true\" given"}]}
{"line":2,"identifier":"apollon_optionb_false","status_code":201}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testCreateAndUpdateProductVariantWithUpdatedAxeValue()
    {
        $data =
            <<<JSON
    {"identifier": "apollon_optionb_true", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": true}]}}
    {"identifier": "apollon_optionb_new", "parent": "amor", "values": {"a_yes_no": [{"locale": null, "scope": null, "data": true}]}}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"identifier":"apollon_optionb_true","status_code":204}
{"line":2,"identifier":"apollon_optionb_new","status_code":422,"message":"Validation failed.","errors":[{"property":"attribute","message":"Cannot set value \"1\" for the attribute axis \"a_yes_no\" on variant product \"apollon_optionb_new\", as the variant product \"apollon_optionb_true\" already has this value"}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    public function testErrorWhenIdentifierIsMissing()
    {
        $data =
            <<<JSON
    {"code": "my_identifier"}
    {"identifier": null}
    {"identifier": ""}
    {"identifier": " "}
    {}
JSON;

        $expectedContent =
            <<<JSON
{"line":1,"status_code":422,"message":"Identifier is missing."}
{"line":2,"status_code":422,"message":"Identifier is missing."}
{"line":3,"status_code":422,"message":"Identifier is missing."}
{"line":4,"status_code":422,"message":"Identifier is missing."}
{"line":5,"status_code":422,"message":"Identifier is missing."}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
