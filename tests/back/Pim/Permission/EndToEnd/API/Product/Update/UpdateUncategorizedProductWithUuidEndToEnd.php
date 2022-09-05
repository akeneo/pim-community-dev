<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateUncategorizedProductWithUuidEndToEnd extends AbstractProductTestCase
{
    const DOC_PATCH_CODE = 'patch_products__code_';

    public function testSuccessfullyUpdateAGrantedLocalizedValueOnUncategorizedProductEvenIfANotGrantedLocaleIsFilled()
    {
        $this->activateLocaleForChannel('de_DE', 'ecommerce');

        $updatedData = [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['locale' => 'de_DE', 'scope' => 'ecommerce', 'data' => 'DE ecommerce']
                ]
            ]
        ];
        $this->updateProduct($updatedData, 'product_without_category');

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}],
        "a_localized_and_scopable_text_area": [
            {
                "locale": "en_US",
                "scope": "ecommerce",
                "data": "Awesome Data !"
            }
        ]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $expectedValues = '{"sku": {"<all_channels>": {"<all_locales>": "product_without_category"}}, ';
        $expectedValues.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "DE ecommerce", "en_US": "Awesome Data !"}}}';

        $sql = 'SELECT p.raw_values FROM pim_catalog_product p WHERE p.identifier = "product_without_category"';
        $this->assertEquals([['raw_values' => $expectedValues]], $this->getDatabaseData($sql));
    }

    public function testFailedUpdateUncategorizedProductWithNotGrantedAttribute()
    {
        $this->updateProduct(
            [
                'values' => [
                    'a_metric_without_decimal_negative' => [
                        ['locale' => null, 'scope' => null, 'data' => ['amount' => '-42', 'unit' => 'CELSIUS']]
                    ]
                ]
            ],
            'product_without_category'
        );

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}],
        "a_metric_without_decimal_negative": [
            {
                "locale": null,
                "scope": null,
                "data": {
                    "amount": "-273",
                    "unit": "CELSIUS"
                }
            }
        ]
    }
}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $expectedMessage = 'The a_metric_without_decimal_negative attribute does not exist in your PIM';
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedUpdateUncategorizedProductWithOnlyViewableAttribute()
    {
        $this->updateProduct(
            [
                'values' => [
                    'a_number_integer' => [
                        ['locale' => null, 'scope' => null, 'data' => '42']
                    ]
                ]
            ],
            'product_without_category'
        );

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}],
        "a_number_integer": [
            {
                "locale": null,
                "scope": null,
                "data": "1"
            }
        ]
    }
}
JSON;
        $expectedMessage = <<<JSON
{
  "code": 403,
  "message": "Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }

    public function testFailedToUpdateUncategorizedProductOnNotGrantedLocale()
    {
        $this->updateProduct(
            [
                'values' => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'de_DE', 'scope' => 'ecommerce', 'data' => 'DE ecommerce']
                    ]
                ]
            ],
            'product_without_category'
        );

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}],
        "a_localized_and_scopable_text_area": [
            {
                "locale": "de_DE",
                "scope": "ecommerce",
                "data": "May the patch works without permission!"
            }
        ]
    }
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);

        $expectedMessage = 'Attribute \"a_localized_and_scopable_text_area\" expects an existing and ' .
            'activated locale, \"de_DE\" given';
        $this->assertError422($client->getResponse(), $expectedMessage, static::DOC_PATCH_CODE);
    }

    public function testFailedToUpdateUncategorizedProductOnOnlyViewableLocale()
    {
        $this->updateProduct(
            [
                'values' => [
                    'a_localized_and_scopable_text_area' => [
                        ['locale' => 'fr_FR', 'scope' => 'ecommerce', 'data' => 'Un texte français.']
                    ]
                ]
            ],
            'product_without_category'
        );

        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}],
        "a_localized_and_scopable_text_area": [
            {
                "locale": "fr_FR",
                "scope": "ecommerce",
                "data": "Qui ne tente rien ..."
            }
        ]
    }
}
JSON;

        $expectedMessage = <<<JSON
{
  "code": 403,
  "message": "You only have a view permission on the locale \"fr_FR\"."
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }

    public function testFailedToUpdateUncategorizedProductWithNotGrantedCategory()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}]
    },
    "categories": ["categoryB"]
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $this->assertError422(
            $client->getResponse(),
            'Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given',
            static::DOC_PATCH_CODE
        );
    }

    public function testUpdateUncategorizedProductWithViewableCategory()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}]
    },
    "categories": ["categoryA2"]
}
JSON;
        $expectedMessage = <<<JSON
{
  "code": 403,
  "message": "You should at least keep your product in one category on which you have an own permission."
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }

    public function testFailedToUpdateUncategorizedProductWithEditGrantedCategory()
    {
        $data = <<<JSON
{
    "values": {
        "sku": [{"locale": null, "scope": null, "data": "product_without_category"}]
    },
    "categories": ["categoryA"]
}
JSON;
        $expectedMessage = <<<JSON
{
  "code": 403,
  "message": "You should at least keep your product in one category on which you have an own permission."
}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $uuid = $this->getProductUuidFromIdentifier('product_without_category')->toString();
        $client->request('PATCH', "api/rest/v1/products-uuid/{$uuid}", [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }
}
