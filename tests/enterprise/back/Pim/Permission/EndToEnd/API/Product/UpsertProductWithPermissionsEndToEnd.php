<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
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
class UpsertProductWithPermissionsEndToEnd extends AbstractProductTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_editable_by_redactor', [
            new SetCategories(['categoryB', 'master']),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'EN ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'FR ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'de_DE', 'DE ecommerce'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB']),
        ]);
    }

    public function testToMergeNotGrantedCategoryWithANewCategory()
    {
        $data = '{"categories": ["categoryA1", "categoryA2", "master"]}';

        $uuid = $this->getProductUuid('product_editable_by_redactor');
        $sql = <<<SQL
SELECT c.code
FROM pim_catalog_product p
INNER JOIN pim_catalog_category_product cp ON p.uuid = cp.product_uuid
INNER JOIN pim_catalog_category c ON c.id = cp.category_id
WHERE uuid = UUID_TO_BIN("{uuid}")
ORDER BY c.code
SQL;
        $sql = \strtr($sql, ['{uuid}' => $uuid->toString()]);

        $this->assert('product_editable_by_redactor', $data, $sql, [
            ['code' => 'categoryA1'],
            ['code' => 'categoryA2'],
            ['code' => 'categoryB'],
            ['code' => 'master'],
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

        $uuid = $this->getProductUuid('product_without_category');
        $sql = <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT t.code, pcpud.raw_data AS identifier
FROM pim_catalog_product p 
    INNER JOIN pim_catalog_association a ON a.owner_uuid = p.uuid
    INNER JOIN pim_catalog_association_type t ON t.id = a.association_type_id
    INNER JOIN pim_catalog_association_product ap ON a.id = ap.association_id
    INNER JOIN pim_catalog_product associated_product ON associated_product.uuid = ap.product_uuid
    LEFT JOIN pim_catalog_product_unique_data pcpud 
        ON pcpud.product_uuid = associated_product.uuid
        AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE p.uuid = UUID_TO_BIN("{uuid}")
ORDER BY t.code, pcpud.raw_data
SQL;
        $sql = \strtr($sql, ['{uuid}' => $uuid->toString()]);

        $this->assert('product_without_category', $data, $sql, [
            ['code' => 'X_SELL', 'identifier' => 'product_not_viewable_by_redactor'],
            ['code' => 'X_SELL', 'identifier' => 'product_viewable_by_everybody_1'],
            ['code' => 'X_SELL', 'identifier' => 'product_viewable_by_everybody_2'],
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

        $uuid = $this->getProductUuid('product_editable_by_redactor');
        $sql = 'SELECT p.raw_values FROM pim_catalog_product p WHERE p.uuid = UUID_TO_BIN("{uuid}")';
        $sql = \strtr($sql, ['{uuid}' => $uuid->toString()]);

        $values = '{"sku": {"<all_channels>": {"<all_locales>": "product_editable_by_redactor"}}, ';
        $values.= '"a_yes_no": {"<all_channels>": {"<all_locales>": true}}, ';
        $values.= '"a_multi_select": {"<all_channels>": {"<all_locales>": ["optionA", "optionB"]}}, ';
        $values.= '"a_localized_and_scopable_text_area": {"tablet": {"de_DE": "DE ecommerce", "fr_FR": "FR ecommerce"}, "ecommerce": {"en_US": "english"}}}';

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
  "message": "Product \"product_viewable_by_everybody_1\" cannot be updated. You only have a view right on this product."
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
