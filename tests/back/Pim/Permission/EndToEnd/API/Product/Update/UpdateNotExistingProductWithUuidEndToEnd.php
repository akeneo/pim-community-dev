<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UpdateNotExistingProductWithUuidEndToEnd extends AbstractProductTestCase
{
    const DOC_PATCH_CODE = 'patch_products__code_';

    public function testFailedToUpdateNotExistingProductWithNotGrantedAttribute()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}],
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
        $expectedMessage = 'The a_metric_without_decimal_negative attribute does not exist in your PIM';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedToUpdateNotExistingProductWithOnlyViewGrantedAttribute()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}],
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
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

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
    ],
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}]
    }
}
JSON;
        $expectedMessage = 'Property \"categories\" expects a valid category code. ' .
            'The category does not exist, \"categoryB\" given';

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testSuccessfullyUpdateNotExistingProductWithAnOnlyViewableCategory()
    {
        $data = <<<JSON
{
    "categories": [
        "categoryA2"
    ],
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $product = $this->get('pim_catalog.repository.product')->find($uuid);
        $this->assertSame(['categoryA2'], $product->getCategoryCodes());
    }

    public function testFailedToUpdateNotExistingProductWithNotGrantedLocale()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}],
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
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedToUpdateNotExistingProductWithOnlyViewGrantedLocale()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}],
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
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testFailedToUpdateAssociationOfNotExistingProductWithNotGrantedCategory()
    {
        $productNotViewableByRedactor = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor')->toString();
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "not_a_product"}]
    },
    "associations": {
        "PACK": {
            "products": ["{$productNotViewableByRedactor}"]
        }
    }
}
JSON;
        $expectedResponseContent = <<<JSON
{"code":422,"message":"Property \"associations\" expects a valid product uuid. The product does not exist, \"{$productNotViewableByRedactor}\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = Uuid::uuid4()->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }
}
