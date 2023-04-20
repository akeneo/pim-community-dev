<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\VariantProduct\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductToVariantEndToEnd extends AbstractProductTestCase
{
    use AssertEventCountTrait;

    private string $productUuid;

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
            'uuid' => $this->productUuid,
            'identifier' => 'product_family_variant',
            'family' => 'familyA',
            'parent' => 'amor',
            'groups' => [],
            'categories' => ['categoryA2', 'master'],
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
            'quantified_associations' => [],
        ];

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEventCount(1, ProductUpdated::class);

        $this->assertSameProducts($expectedProduct, 'product_family_variant');

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
                        'message' => 'The variant product cannot have product model ' .
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
            new SetFamily('familyA3'),
            new SetCategories(['master']),
            new SetBooleanValue('a_yes_no', null, null, true)
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
            new SetFamily('familyA'),
            new SetCategories(['categoryA2']),
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
        $this->productUuid = $this->createProduct('product_family_variant', [
            new SetFamily('familyA'),
            new SetCategories(['categoryA2']),
            new SetBooleanValue('a_yes_no', null, null, true)
        ])->getUuid()->toString();
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
