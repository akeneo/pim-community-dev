<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Create;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-----------------------------------------------------------------------------------------+
 * |          |             Locales               |                  Attribute groups                   |
 * +  Roles   +-----------------------------------+-----------------------------------+-----------------+
 * |          |   en_US   |   fr_FR   |   de_DE   | attributeGroupA | attributeGroupB | attributeGroupC |
 * +----------+-----------------------------------------------------------------------------------------+
 * | Redactor | View,Edit |    View   |     -     |    View,Edit    |      View       |        -        |
 * | Manager  | View,Edit | View,Edit | View,Edit |    View,Edit    |    View,Edit    |    View,Edit    |
 * +----------+-----------------------------------------------------------------------------------------+
 *
 * +------------------------------------+-----------------+
 * |             Attribute              | Attribute group |
 * +------------------------------------+-----------------+
 * | a_date                             | attributeGroupA |
 * | a_localized_and_scopable_text_area | attributeGroupA |
 * | a_number_float                     | attributeGroupB |
 * | a_metric_without_decimal_negative  | attributeGroupC |
 * +------------------------------------+-----------------+
 */
class AddAttributeOnProductWithUuidAndPermissionsEndToEnd extends AbstractProductTestCase
{
    ### Tests for redactor
    public function testErrorProductWithNotGrantedAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_metric_without_decimal_negative": [
            {"data": {"amount": "-1", "unit": "CELSIUS"}, "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $this->assertError422(
            $client->getResponse(),
            'The a_metric_without_decimal_negative attribute does not exist in your PIM',
            'post_products_uuid'
        );
    }

    public function testErrorProductWithOnlyViewableAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_number_float": [
            {"data": "10.50", "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $expected = '{"code": 403, "message": "Attribute \"a_number_float\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}';
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testSuccessProductWithEditableAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_number_float": [
            {"data": "10.50146513500", "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $expectedProduct = [
            'uuid'          => $this->getProductUuidFromIdentifier('my_product')->toString(),
            'identifier'    => 'my_product',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku'            => [['locale' => null, 'scope' => null, 'data' => 'my_product']],
                'a_number_float' => [['locale' => null, 'scope' => null, 'data' => '10.501465135']],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }

    ### LOCALIZABLE ATTRIBUTE
    public function testErrorProductWithNotGrantedLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_localized_and_scopable_text_area": [
            {"data": "DE ecommerce", "locale": "de_DE", "scope": "tablet"}
        ]
    }
}
JSON;
        $expectedErrorMessage = 'Attribute \"a_localized_and_scopable_text_area\" expects an existing and ' .
            'activated locale, \"de_DE\" given';
        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedErrorMessage, 'post_products_uuid');
    }

    public function testErrorProductWithOnlyViewableLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_localized_and_scopable_text_area": [
            {"data": "10.50", "locale": "fr_FR", "scope": "tablet"}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame('{"code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}', $response->getContent());
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testSuccessProductWithEditableLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_localized_and_scopable_text_area": [
            {"data": "10.50", "locale": "en_US", "scope": "ecommerce"}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $expectedProduct = [
            'uuid'          => $this->getProductUuidFromIdentifier('my_product')->toString(),
            'identifier'    => 'my_product',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku'                                => [['locale' => null, 'scope' => null, 'data' => 'my_product']],
                'a_localized_and_scopable_text_area' => [['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => '10.50']],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }

    ### Tests for manager
    public function testSuccessProductWithGrantedAttributeForManager()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "my_product"}],
        "a_metric_without_decimal_negative": [
            {"data": {"amount": "-1", "unit": "CELSIUS"}, "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('POST', '/api/rest/v1/products-uuid', [], [], [], $data);
        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $expectedProduct = [
            'uuid'          => $this->getProductUuidFromIdentifier('my_product')->toString(),
            'identifier'    => 'my_product',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku'                               => [['locale' => null, 'scope' => null, 'data' => 'my_product']],
                'a_metric_without_decimal_negative' => [['locale' => null, 'scope' => null, 'data' => ['amount' => -1, 'unit' => 'CELSIUS']]],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
            'quantified_associations' => [],
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }
}
