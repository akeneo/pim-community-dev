<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Update;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateNotExistingProductIntegration extends AbstractProductTestCase
{
    const DOC_PATCH_CODE = 'patch_products__code_';

    public function testFailedToUpdateNotExistingProductWithNotGrantedAttribute()
    {
        $data = <<<JSON
{
    "values": {
        "a_metric_without_decimal_negative": [
            {
                "data": {
                    "amount": "-273",
                    "unit": "CELSIUS"
                },
                "locale": null,
                "scope": null
            }
        ]
    }
}
JSON;
        $expectedMessage = 'Property \"a_metric_without_decimal_negative\" does not exist';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedToUpdateNotExistingProductWithOnlyViewGrantedAttribute()
    {
        $data = <<<JSON
{
    "values": {
        "a_number_integer": [
            {
                "data": 42,
                "locale": null,
                "scope": null
            }
        ]
    }
}
JSON;
        $expectedResponseContent = <<<JSON
{"code":403,"message":"Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testFailedToUpdateNotExistingProductWithNotGrantedCategory()
    {
        $data = <<<JSON
{
    "categories": [
        "categoryB"
    ]
}
JSON;
        $expectedMessage = 'Property \"categories\" expects a valid category code. ' .
            'The category does not exist, \"categoryB\" given';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testSuccessfullyUpdateNotExistingProductWithAnOnlyViewableCategory()
    {
        $data = <<<JSON
{
    "categories": [
        "categoryA2"
    ]
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('not_a_product');
        $this->assertSame(['categoryA2'], $product->getCategoryCodes());
    }

    public function testFailedToUpdateNotExistingProductWithNotGrantedLocale()
    {
        $data = <<<JSON
{
    "values": {
        "a_localized_and_scopable_text_area": [
            {
                "data": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed consequat aliquet sem.",
                "locale": "de_DE",
                "scope": "tablet"
            }
        ]
    }
}
JSON;
        $expectedMessage = 'Attribute \"a_localized_and_scopable_text_area\" expects an existing and ' .
            'activated locale, \"de_DE\" given';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedToUpdateNotExistingProductWithOnlyViewGrantedLocale()
    {
        $data = <<<JSON
{
    "values": {
        "a_localized_and_scopable_text_area": [
            {
                "data": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed consequat aliquet sem.",
                "locale": "fr_FR",
                "scope": "tablet"
            }
        ]
    }
}
JSON;
        $expectedResponseContent = <<<JSON
{"code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testFailedToUpdateAssociationOfNotExistingProductWithNotGrantedCategory()
    {
        $data = <<<JSON
{
    "associations": {
        "PACK": {
            "products": [
                "product_not_viewable_by_redactor"
            ]
        }
    }
}
JSON;
        $expectedResponseContent = <<<JSON
{"code":403,"message":"You cannot associate a product on which you have not a view permission."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/not_a_product', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }
}
