<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+--------------------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |                   Categories               |             Locales               |                   Attribute groups                  |
 * +  Roles   +--------------------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |    master     |   categoryA2  |  categoryB |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +==========+===============================+===================================+==================================================================+
 * | Redactor | View,Edit,Own |     View      |       -    | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit  | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |                 |
 * +================+===============================+================================+===============================================================+
 */
class UpsertProductWithPermissionsIntegration extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('product_editable_by_redactor', [
            'categories'   => ['categoryB', 'master'],
            'values'       => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce']
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ]
            ]
        ]);
    }

    public function testToMergeNotGrantedCategoryWithANewCategory()
    {
        $data = '{"categories": ["categoryA1", "categoryA2", "master"]}';

        $expected = [
            'identifier'    => 'product_editable_by_redactor',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'variant_group' => null,
            'enabled'       => true,
            'associations'  => [],
            'categories'    => ['categoryA1', 'categoryA2', 'categoryB', 'master'],
            'created'       => '2017-08-08T19:28:43+02:00',
            'updated'       => '2017-08-08T19:28:43+02:00',
            'values'        => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce'],
                    ['data' => 'EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'sku'      => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_editable_by_redactor'],
                ]
            ]
        ];

        $this->assert('product_editable_by_redactor', $data, $expected);
    }

    public function testToMergeNotGrantedAssociationWithANewAssociation()
    {
        $data = <<<JSON
{
    "associations": {
        "X_SELL": {
            "products": ["product_viewable_by_everybody_1", "product_viewable_by_everybody_2"]
        }
    }
}
JSON;
        $expected = [
            'identifier'    => 'product_without_category',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'variant_group' => null,
            'enabled'       => true,
            'categories'    => [],
            'created'       => '2017-08-08T19:28:43+02:00',
            'updated'       => '2017-08-08T19:28:43+02:00',
            'values'        => [
                'sku' => [
                    ['data' => 'product_without_category', 'locale' => null, 'scope' => null]
                ]
            ],
            'associations'  => [
                'X_SELL' => [
                    'products' => [
                        'product_viewable_by_everybody_1', 'product_viewable_by_everybody_2', 'product_not_viewable_by_redactor'
                    ],
                    'groups'   => []
                ],
                'PACK' => [
                    'products' => [],
                    'groups'   => []
                ],
                'SUBSTITUTION' => [
                    'products' => [],
                    'groups'   => []
                ],
                'UPSELL' => [
                    'products' => [],
                    'groups'   => []
                ]
            ]
        ];

        $this->assert('product_without_category', $data, $expected);
    }

    public function testToMergeNotGrantedValuesWithANewValue()
    {
        $data = <<<JSON
{
    "values": {
        "a_yes_no": [
            { "data": true, "locale": null, "scope": null }
        ],
        "a_localized_and_scopable_text_area": [
            { "data": "english", "locale": "en_US", "scope": "ecommerce" }
        ]
    }
}
JSON;

        $expected = [
            'identifier'    => 'product_editable_by_redactor',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'variant_group' => null,
            'enabled'       => true,
            'associations'  => [],
            'categories'    => ['categoryB', 'master'],
            'created'       => '2017-08-08T19:28:43+02:00',
            'updated'       => '2017-08-08T19:28:43+02:00',
            'values'        => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce'],
                    ['data' => 'english', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_yes_no' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sku'      => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_editable_by_redactor'],
                ]
            ]
        ];

        $this->assert('product_editable_by_redactor', $data, $expected);
    }

    public function testToLostTheOwnershipOfAProduct()
    {
        $data = '{"categories": ["categoryA"]}';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_editable_by_redactor', [], [], [], $data);

        $expected = <<<JSON
{
  "code": 403,
  "message": "You should at least keep your product in one category on which you have an own permission."
}
JSON;

        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }

    public function testToUpdateAViewableProduct()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], '{}');
        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());

        $expected = <<<JSON
{
  "code": 403,
  "message": "Product \"product_viewable_by_everybody_1\" cannot be updated. It should be at least in an own category."
}
JSON;

        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }

    public function testToUpdateAViewableProductWithoutChange()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"enabled": true}';
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResponse()->getContent());
    }

    /**
     * @param string $identifier                code of the product
     * @param string $data                      data submitted
     * @param array  $expectedProductNormalized expected product data normalized in standard format
     */
    private function assert(string $identifier, string $data, array $expectedProductNormalized)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $productNormalized = $this->get('pim_catalog.normalizer.standard.product')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($expectedProductNormalized);
        NormalizedProductCleaner::clean($productNormalized);

        $this->assertEquals($expectedProductNormalized, $productNormalized);
    }
}
