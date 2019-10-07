<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductToVariantEndToEnd extends AbstractProductTestCase
{
    public function testUpdateProductToVariant()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "parent": "amor"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant', [], [], [], $data);
        $response = $client->getResponse();

        $expectedProduct = [
            'identifier' => 'product_family_variant',
            'family' => 'familyA',
            'parent' => 'amor',
            'groups' => [],
            'enabled' => true,
            'values' => [
                'a_localized_and_scopable_text_area' => [['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'my pink tshirt']],
                'a_number_float' => [['locale' => null, 'scope' => null, 'data' => '12.5000']],
                'a_price'  => [
                    'data' => ['locale' => null, 'scope' => null, 'data' => [['amount' => '50.00', 'currency' => 'EUR']]],
                ],
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
                'a_yes_no' => [['locale' => null, 'scope' => null, 'data' => true]],
                'sku' => [['locale' => null, 'scope' => null, 'data' => 'product_family_variant']],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
        ];

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_family_variant');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        unset($standardizedProduct['categories']);
        NormalizedProductCleaner::clean($expectedProduct);
        NormalizedProductCleaner::clean($standardizedProduct);
        $this->assertEquals($standardizedProduct, $expectedProduct);

        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/products/product_family_variant',
            $response->headers->get('location')
        );
    }

    public function testProductModelDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "parent": "mayonnaise"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(
            [
                'code' => 422,
                'message' => 'The given product model "mayonnaise" does not exist'
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testFamilyHasThreeLevelsAndProductCanNotBeAssociatedToRootProductModel()
    {
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "parent": "test"
    }
JSON;
        $client->request('PATCH', 'api/rest/v1/products/product_family_variant', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(
            [
                'code' => 422,
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'property' => 'attribute',
                        'message' => 'Attribute "a_simple_select" cannot be empty, as it is defined as an ' .
                            'axis for this entity'
                    ],
                    [
                        'property' => 'parent',
                        'message' => 'The variant product "product_family_variant" cannot have product model ' .
                            '"test" as parent, (this product model can only have other product models as children)'
                    ],
                    [
                        'property' => 'attribute',
                        'message' => 'Cannot set the property "sku" to this entity as it is not in the attribute set'
                    ]
                ]
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testProductHasNotTheSameFamilyThanTheProductModel()
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->createProduct('product_familyA3', [
            'family' => 'familyA3',
            'categories' => ['master'],
            'values' => [
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
            ]
        ]);
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "parent": "amor"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_familyA3', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(
            [
                'code' => 422,
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'property' => 'family',
                        'message' => 'The variant product family must be the same than its parent'
                    ]
                ],
            ],
            json_decode($response->getContent(), true)
        );
    }

    public function testProductHasNoValueForTheVariantAxis()
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->createProductModel(
            [
                'code' => 'parent_product_no_value',
                'family_variant' => 'familyVariantA2',
                'values'  => []
            ]
        );

        $this->createProduct('product_no_value', [
            'family' => 'familyA',
            'categories' => ['categoryA2'],
            'values' => []
        ]);
        $client = $this->createAuthenticatedClient();
        $data =
<<<JSON
    {
        "parent": "parent_product_no_value"
    }
JSON;

        $client->request('PATCH', 'api/rest/v1/products/product_no_value', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame(
            [
                'code' => 422,
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'property' => 'attribute',
                        'message' => 'Attribute "a_simple_select" cannot be empty, as it is defined as an axis for this entity'
                    ]
                ]
            ],
            json_decode($response->getContent(), true)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
        $this->createProductModel([
            'code' => 'amor',
            'parent' => 'test',
            'categories' => ['master'],
            'family_variant' => 'familyVariantA1',
            'values'  => [
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']]
            ],
        ]);
        $this->createProduct('product_family_variant', [
            'family' => 'familyA',
            'categories' => ['categoryA2'],
            'values' => [
                'a_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]
            ]
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('doctrine.orm.default_entity_manager')->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
