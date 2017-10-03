<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Create;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
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
class AddAttributeOnProductWithPermissionsIntegration extends AbstractProductTestCase
{
    ### Tests for redactor
    public function testErrorProductWithNotGrantedAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_metric_without_decimal_negative": [
            {"data": {"amount": "-1", "unit": "CELSIUS"}, "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $this->assertError422(
            $client->getResponse(),
            'Property \"a_metric_without_decimal_negative\" does not exist',
            'post_products'
        );
    }

    public function testErrorProductWithOnlyViewableAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_number_float": [
            {"data": "10.50", "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = '{"code": 403, "message": "Attribute \"a_number_float\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}';
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testSuccessProductWithEditableAttributeForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_number_float": [
            {"data": "10.50", "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $expectedProduct = [
            'identifier'    => 'my_product',
            'family'        => null,
            'parent'        => null,
            'groups'        => [],
            'categories'    => [],
            'enabled'       => true,
            'values'        => [
                'sku'            => [['locale' => null, 'scope' => null, 'data' => 'my_product']],
                'a_number_float' => [['locale' => null, 'scope' => null, 'data' => '10.5000']],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [],
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }

    ### LOCALIZABLE ATTRIBUTE
    public function testErrorProductWithNotGrantedLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_localized_and_scopable_text_area": [
            {"data": "DE ecommerce", "locale": "de_DE", "scope": "ecommerce"}
        ]
    }
}
JSON;
        $expectedErrorMessage = 'Attribute \"a_localized_and_scopable_text_area\" expects an existing and ' .
            'activated locale, \"de_DE\" given';
        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedErrorMessage, 'post_products');
    }

    public function testErrorProductWithOnlyViewableLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_localized_and_scopable_text_area": [
            {"data": "10.50", "locale": "fr_FR", "scope": "ecommerce"}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame('{"code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}', $response->getContent());
    }

    public function testSuccessProductWithEditableLocaleForRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_localized_and_scopable_text_area": [
            {"data": "10.50", "locale": "en_US", "scope": "ecommerce"}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $expectedProduct = [
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
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }

    ### Tests for manager
    public function testSuccessProductWithGrantedAttributeForManager()
    {
        $data = <<<JSON
{
    "identifier": "my_product",
    "values": {
        "a_metric_without_decimal_negative": [
            {"data": {"amount": "-1", "unit": "CELSIUS"}, "locale": null, "scope": null}
        ]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $client->request('POST', '/api/rest/v1/products', [], [], [], $data);
        $this->assertSame(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $expectedProduct = [
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
        ];

        $this->assertSameProducts($expectedProduct, 'my_product');
    }
}
