<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |          Categories           |             Locales               |                   Attribute groups                  |
 * +  Roles   +-------------------------------+-----------------------------------+-----------------------------------------------------+
 * |          |   categoryA2  |   categoryB   |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +==========+===============================+===================================+=====================================================+
 * | Redactor |      View     |       -       | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +================+===============================+===================================+===============================================+
 */
class UpsertProductWithPermissionsIntegration extends AbstractProductTestCase
{
    public function testToMergeNotGrantedCategoryWithANewCategory()
    {
        $data = '{"categories": ["categoryA1", "categoryA2"]}';

        $expected = [
            'identifier'    => 'product_viewable_by_everybody_2',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'enabled'       => true,
            'categories'    => ['categoryA1', 'categoryA2', 'categoryB'],
            'created'       => '2017-08-08T19:28:43+02:00',
            'updated'       => '2017-08-08T19:28:43+02:00',
            'values'        => [
                'sku' => [
                    ['data' => 'product_viewable_by_everybody_2', 'locale' => null, 'scope' => null]
                ]
            ],
            'associations'  => []
        ];

        $this->assert('product_viewable_by_everybody_2', $data, $expected);
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
            'identifier'    => 'product_viewable_by_everybody_1',
            'family'        => null,
            'groups'        => [],
            'variant_group' => null,
            'enabled'       => true,
            'associations'  => [],
            'categories'    => ['categoryA2'],
            'created'       => '2017-08-08T19:28:43+02:00',
            'updated'       => '2017-08-08T19:28:43+02:00',
            'values'        => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce'],
                    ['data' => 'english', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
                'a_number_float' => [['data' => '12.05', 'locale' => null, 'scope' => null]],
                'a_localizable_image' => [
                    ['data' => '4/7/c/4/47c44269d7379568daf03f85192b240199d82055_akeneo.jpg', 'locale' => 'de_DE', 'scope' => null],
                    ['data' => '4/7/c/4/47c44269d7379568daf03f85192b240199d82055_akeneo.jpg', 'locale' => 'en_US', 'scope' => null],
                    ['data' => '4/7/c/4/47c44269d7379568daf03f85192b240199d82055_akeneo.jpg', 'locale' => 'fr_FR', 'scope' => null],
                ],
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_yes_no' => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
                'sku'      => [
                    ['locale' => null, 'scope' => null, 'data' => 'product_viewable_by_everybody_1'],
                ]
            ]
        ];

        $this->assert('product_viewable_by_everybody_1', $data, $expected);
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
