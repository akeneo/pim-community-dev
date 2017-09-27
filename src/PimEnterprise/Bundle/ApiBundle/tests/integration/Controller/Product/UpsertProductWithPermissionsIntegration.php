<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

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

        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product p
INNER JOIN pim_catalog_category_product cp ON p.id = cp.product_id
INNER JOIN pim_catalog_category c ON c.id = cp.category_id
WHERE identifier = "product_editable_by_redactor"
SQL;

        $this->assert('product_editable_by_redactor', $data, $sql, [
            ['code' => 'master'],
            ['code' => 'categoryA1'],
            ['code' => 'categoryA2'],
            ['code' => 'categoryB'],
        ]);
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

        $sql = <<<SQL
SELECT t.code, associated_product.identifier
FROM pim_catalog_product p 
INNER JOIN pim_catalog_association a ON a.owner_id = p.id
INNER JOIN pim_catalog_association_type t ON t.id = a.association_type_id
INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
INNER JOIN pim_catalog_product associated_product ON associated_product.id = ap.product_id 
WHERE p.identifier = "product_without_category"
SQL;

        $this->assert('product_without_category', $data, $sql, [
            ['code' => 'X_SELL', 'identifier' => 'product_viewable_by_everybody_1'],
            ['code' => 'X_SELL', 'identifier' => 'product_viewable_by_everybody_2'],
            ['code' => 'X_SELL', 'identifier' => 'product_not_viewable_by_redactor'],
        ]);
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

        $sql = 'SELECT p.raw_values FROM pim_catalog_product p WHERE p.identifier = "product_editable_by_redactor"';

        $values = '{"sku": {"<all_channels>": {"<all_locales>": "product_editable_by_redactor"}}, ';
        $values.= '"a_yes_no": {"<all_channels>": {"<all_locales>": true}}, ';
        $values.= '"a_multi_select": {"<all_channels>": {"<all_locales>": ["optionA", "optionB"]}}, ';
        $values.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "DE ecommerce", "en_US": "english", "fr_FR": "FR ecommerce"}}}';

        $expected = [['raw_values' => $values]];

        $this->assert('product_editable_by_redactor', $data, $sql, $expected);
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

    public function testToUpdateAViewableProductWithChange()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $data = '{"enabled": false}';
        $client->request('PATCH', 'api/rest/v1/products/product_viewable_by_everybody_1', [], [], [], $data);
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
     * @param string $sql                       SQL for database query
     * @param array  $expectedProductNormalized expected product data normalized in standard format
     */
    private function assert(string $identifier, string $data, string $sql, array $expectedProductNormalized)
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('PATCH', 'api/rest/v1/products/' . $identifier, [], [], [], $data);
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $this->assertEquals($expectedProductNormalized, $this->getDatabaseData($sql));
    }
}
