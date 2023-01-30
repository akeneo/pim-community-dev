<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Response;

class PartialUpdateProductModelEndToEnd extends AbstractProductModelTestCase
{
    use AssertEventCountTrait;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->createProduct('any_product');

        $this->createProductModel(
            [
                'code' => 'sub_sweat',
                'parent' => 'sweat',
                'family_variant' => 'familyVariantA1',
                'categories' => ['master', 'categoryA'],
                'values'  => [
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'optionB',
                        ],
                    ],
                ],
                'associations' => [
                    'SUBSTITUTION' => [
                        'groups' => [],
                        'products' => ['any_product'],
                        'product_models' => []
                    ],
                ],
            ],
        );

        $this->createVariantProduct('apollon_optionb_false', [
            new SetCategories(['categoryB']),
            new ChangeParent('sub_sweat'),
            new SetBooleanValue('a_yes_no', null, null, false)
        ]);
    }

    public function testUpdateSubProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "family_variant": "familyVariantA1",
    "parent": "sweat",
    "values": {
        "a_text": [
            {
                "locale": null,
                "scope": null,
                "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(0, ProductUpdated::class);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        $this->assertSame($standardizedProduct['values']['a_text'][0]['data'], 'My awesome text');
    }

    public function testUpdateSubProductModelAssociation()
    {
        $this->createProduct('a_second_product');
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "associations": {
        "SUBSTITUTION": {
            "products": ["any_product", "a_second_product"]
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(1, ProductModelUpdated::class);

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat');

        $this->assertCount(2, $productModel->getAssociatedProducts('SUBSTITUTION'));
    }

    public function testRemoveSubProductModelAssociation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "associations": {
        "SUBSTITUTION": {
            "products": []
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(1, ProductModelUpdated::class);

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat');

        $this->assertCount(0, $productModel->getAssociatedProducts('SUBSTITUTION'));
    }

    public function testUpdateSubProductModelCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "categories": ["master", "categoryA", "categoryA1"]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(1, ProductModelUpdated::class);

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat');

        $this->assertCount(3, $productModel->getCategories());
    }

    public function testRemoveSubProductModelCategories()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "categories": ["master"]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(1, ProductModelUpdated::class);

        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('sub_sweat');

        $this->assertCount(1, $productModel->getCategories());
    }

    public function testUpdateSubProductModelWithNonExistingProperty()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "michel": "field"
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"michel\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
          "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

    }

    public function testUpdateSubProductModelWithNoCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "family_variant": "familyVariantA1",
    "parent": "sweat",
    "values": {
        "a_text": [
            {
                "locale": null,
                "scope": null,
                "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        $this->assertSame($standardizedProduct['values']['a_text'][0]['data'], 'My awesome text');
    }

    public function testCreateSubProductModelWithSubProductModelAsParent()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "family_variant": "familyVariantA1",
    "parent": "sub_sweat",
    "values": {
        "a_text": [
            {
                "locale": null,
                "scope": null,
                "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/new_sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"parent\" expects a valid parent code. The new parent of the product model must be a root product model, \"sub_sweat\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http:\/\/api.akeneo.com\/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateAxisSubProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "family_variant": "familyVariantA1",
    "parent": "sweat",
    "values": {
        "a_simple_select": [
            {
            "locale": null,
            "scope": null,
            "data": "optionA"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUpdateSubProductModelWithNoParentGiven()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "family_variant": "familyVariantA1",
    "values": {
        "a_text": [
            {
              "locale": null,
              "scope": null,
              "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testUpdateSubProductModelWithNoParentAndFamilyGiven()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "values": {
        "a_text": [
            {
            "locale": null,
            "scope": null,
            "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sub_sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testUpdateSubProductModelWithDifferentFamilyThanParent()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "family_variant": "familyVariantA2",
    "parent": "sweat",
    "values": {
        "a_text": [
            {
            "locale": null,
            "scope": null,
            "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"family_variant\" cannot be modified, \"familyVariantA2\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
          "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "family_variant": "familyVariantA1",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
        $this->assertEventCount(0, ProductUpdated::class);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_optionb_false');
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        $this->assertSame($standardizedProduct['values']['a_number_float'][0]['data'], '15.3000');

        $this->assertEventCount(1, ProductModelUpdated::class);
    }

    public function testUpdateCaseInsensitiveProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "code": "sweat",
    "family_variant": "fAmIlYVaRiAnTA1"
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sweat',
            $response->headers->get('location')
        );
    }

    public function testSubProductModelCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_product_model",
    "family_variant": "familyVariantA1",
    "parent": "sweat",
    "values": {
        "a_simple_select": [
            {
                "locale": null,
                "scope": null,
                "data": "optionA"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_product_model', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'sub_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => 'sweat',
            'categories'     => [],
            'values'        => [
                'a_price'                            => [
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
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'sub_product_model');
    }

    public function testRootProductModelCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "root_product_model",
    "family_variant": "familyVariantA1",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"12.5000"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/root_product_model', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => null,
            'categories'     => [],
            'values'        => [
                'a_number_float' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'root_product_model');
    }

    public function testRootProductModelCreationWithParentToNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "root_product_model",
    "family_variant": "familyVariantA1",
    "parent": null,
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"12.5000"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/root_product_model', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => null,
            'categories'     => [],
            'values'        => [
                'a_number_float' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'root_product_model');
    }

    public function testRootProductModelUpdateWithParentToNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "family_variant": "familyVariantA1",
    "parent": null,
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"12.5000"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testSubProductModelUpdateWithParentToNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "parent": null,
    "family_variant": "familyVariantA1",
    "parent": null,
    "values": {
        "a_simple_select":[
            {
                "locale":null,
                "scope":null,
                "data":"optionB"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"parent\" cannot be modified, \"NULL\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
          "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();


        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithNoFamilyGiven()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame(
            'http://localhost/api/rest/v1/product-models/sweat',
            $response->headers->get('location')
        );
        $this->assertSame('', $response->getContent());
    }

    public function testUpdateSubProductModelWithNonExistingAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "values": {
        "non_existing_attribute":[
            {
                "locale":null,
                "scope":null,
                "data":"trololo le texte"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"non_existing_attribute\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
          "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithNonExistingAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "values": {
        "a_description":[
            {
                "locale":null,
                "scope":null,
                "data":"trololo le texte"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"a_description\" does not exist. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithNoCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "family_variant": "familyVariantA1",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"12.5000"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testUpdateSubProductModelWithDifferentCodeInUrlThanInData()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "new_sub_sweat",
    "parent": "sweat",
    "values": {
        "a_simple_select":[
            {
                "locale":null,
                "scope":null,
                "data":"optionA"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/wrong_code', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "The code \"new_sub_sweat\" provided in the request body must match the code \"wrong_code\" provided in the url."
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithDifferentCodeInUrlThanInData()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "new_root_sweat",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/wrong_code', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "The code \"new_root_sweat\" provided in the request body must match the code \"wrong_code\" provided in the url."
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateSubProductModelWithSameAxes()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat_bis",
    "parent": "sweat",
    "values": {
        "a_simple_select":[
            {
                "locale":null,
                "scope":null,
                "data":"optionB"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat_bis', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "attribute",
            "message": "Cannot set value \"[optionB]\" for the attribute axis \"a_simple_select\" on product model \"sub_sweat_bis\", as the product model \"sub_sweat\" already has this value"
        }
    ]
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithAParent()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "parent": "hat",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"parent\" cannot be modified, \"hat\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithErrorOnFileExtension()
    {
        $client = $this->createAuthenticatedClient();

        $pdfPath = $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'));

        $data =
            <<<JSON
    {
        "code": "new_root_sweat",
        "family_variant": "familyVariantA1",
        "values": {
            "a_file":[
                {
                    "locale":null,
                    "scope":null,
                    "data": "$pdfPath"
                }
            ]
        }
    }
JSON;

        $expectedContent =
            <<<JSON
    {
        "code": 422,
        "message": "Validation failed.",
        "errors": [
            {
                "property": "values",
                "message": "The jpg file extension is not allowed for the a_file attribute. Allowed extensions are pdf, doc, docx, txt.",
                "attribute": "a_file",
                "locale": null,
                "scope": null
            }
        ]
    }
JSON;


        $client->request('PATCH', 'api/rest/v1/product-models/new_root_sweat', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreateASubProductModelWithAFamilyWithOnlyOneLevelOfVariation()
    {
        $this->createProductModel(
            [
                'code' => 'root_product_model',
                'family_variant' => 'familyVariantA2',
                'values'  => [
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                ]
            ]
        );

        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_product",
    "parent": "root_product_model",
    "values": {
        "a_simple_select":[
            {
                "locale":null,
                "scope":null,
                "data":"optionB"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_product', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "parent",
            "message": "The product model \"sub_product\" cannot have a parent"
        }
    ]
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithANewFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "family_variant": "familyVariantA2",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "scope":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"family_variant\" cannot be modified, \"familyVariantA2\" given. Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateSubProductModelWithMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "parent": "sweat",
    "family_variant": "familyVariantA1",
    "values": {
      "a_simple_select": [
        {
          "locale": null,
          "data": "optionB"
        }
      ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"a_simple_select\" expects an array with the key \"scope\". Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testUpdateRootProductModelWithMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
{
    "code": "sweat",
    "family_variant": "familyVariantA1",
    "values": {
        "a_number_float":[
            {
                "locale":null,
                "data":"15.3"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sweat', [], [], [], $data);

        $expectedContent =
            <<<JSON
{
    "code": 422,
    "message": "Property \"a_number_float\" expects an array with the key \"scope\". Check the expected format on the API documentation.",
    "_links": {
        "documentation": {
            "href": "http://api.akeneo.com/api-reference.html#patch_product_models__code_"
        }
    }
}
JSON;

        $response = $client->getResponse();

        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testAccessDeniedWhenPartialUpdateOnProductModelWithoutTheAcl()
    {
        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_edit');

        $data =
            <<<JSON
{
    "code": "sub_sweat",
    "family_variant": "familyVariantA1",
    "parent": "sweat",
    "values": {
        "a_text": [
            {
                "locale": null,
                "scope": null,
                "data": "My awesome text"
            }
        ]
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/product-models/sub_sweat', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @param array  $expectedProductModel normalized data of the product model that should be created
     * @param string $identifier           identifier of the product that should be created
     */
    protected function assertSameProductModels(array $expectedProductModel, $identifier)
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($identifier);
        $standardizedProductModel = $this->get('pim_standard_format_serializer')->normalize($productModel, 'standard');

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
