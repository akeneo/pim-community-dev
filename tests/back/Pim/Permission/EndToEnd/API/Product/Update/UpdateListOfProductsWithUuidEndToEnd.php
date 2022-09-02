<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\Update;

use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class UpdateListOfProductsWithUuidEndToEnd extends AbstractProductTestCase
{
    public function testFailedToUpdateListOfProductsIfHeaderIsWrong()
    {
        $data = <<<JSON
[
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
]
JSON;
        $expectedMessage = <<<JSON
{"code":415,"message":"\"application\/json\" in \"Content-Type\" header is not valid. Only \"application\/vnd.akeneo.collection+json\" is allowed."}
JSON;

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products-uuid', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }

    public function testFailedToUpdateListOfNotExistingProductsWithNotGrantedAttribute()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}], "a_metric_without_decimal_negative": [{"data": {"amount": "-273", "unit": "CELSIUS"}, "locale": null, "scope": null}]}}
    {"uuid": "{$uuid2->toString()}", "values": { "sku": [{"locale": null, "scope": null, "data": "not_existing_product"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"The a_metric_without_decimal_negative attribute does not exist in your PIM. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateAListOfNotExistingProductsWithOnlyViewGrantedAttribute()
    {

        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}],"a_number_integer": [{"data": 42, "locale": null, "scope": null}]}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfNotExistingProductsWithNotGrantedCategory()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryB"], "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}]}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testSuccessfullyUpdateListOfNotExistingProductsWithAnOnlyViewableCategory()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryA2"], "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}]}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","status_code":201}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('not_existing_product');
        $this->assertSame(['categoryA2'], $product->getCategoryCodes());
    }

    public function testFailedToUpdateNotExistingProductsWithNotGrantedLocale()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}], "a_localized_and_scopable_text_area": [{"data": "Lorem ipsum dolor sit amet.", "locale": "de_DE", "scope": "tablet"}]}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"Attribute \"a_localized_and_scopable_text_area\" expects an existing and activated locale, \"de_DE\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfNotExistingProductsWithOnlyViewGrantedLocale()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}], "a_localized_and_scopable_text_area": [{"data": "Lorem ipsum dolor sit amet.", "locale": "fr_FR", "scope": "tablet"}]}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;

        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateAssociationOfListOfNotExistingProductsWithNotGrantedCategory()
    {
        $uuid = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "not_existing_product"}]}, "associations": {"PACK": {"products": ["product_not_viewable_by_redactor"]}}}
    {"uuid": "{$uuid2->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"Property \"associations\" expects an array with valid data, association format is not valid for the association type \"PACK\", \"product_uuids\" expects an array of valid uuids.. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$uuid2->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfProductsNotViewableByUser()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor');
        $newUuid = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}","values": {"sku": [{"locale": null, "scope": null, "data": "product_not_viewable_by_redactor"}], "a_localized_and_scopable_text_area": [{"data": "Awesome Data !", "locale": "en_US", "scope": "ecommerce"}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":404,"message":"Product \"product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testSuccessfullyUpdateAListOfProducts()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "product_without_category"}], "a_text": [{"data": "the text", "locale": null, "scope": null}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","status_code":204}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $this->assertSame('the text', $product->getValue('a_text')->getData());
    }

    public function testSuccessfullyUpdateAGrantedLocalizedValueOnUncategorizedListOfProductsEvenIfANotGrantedLocaleIsFilled()
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

        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"a_localized_and_scopable_text_area": [{"locale": "en_US", "scope": "ecommerce", "data": "Awesome Data !"}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $expectedResponse = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","status_code":204}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;

        $this->assertSame($expectedResponse, $response['content']);

        $expectedValues = '{"sku": {"<all_channels>": {"<all_locales>": "product_without_category"}}, ';
        $expectedValues.= '"a_localized_and_scopable_text_area": {"ecommerce": {"de_DE": "DE ecommerce", "en_US": "Awesome Data !"}}}';

        $sql = 'SELECT p.raw_values FROM pim_catalog_product p WHERE p.identifier = "product_without_category"';
        $this->assertEquals([['raw_values' => $expectedValues]], $this->getDatabaseData($sql));
    }

    public function testFailedUpdateUncategorizedListOfProductsWithNotGrantedAttribute()
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

        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();

        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "values": {"a_metric_without_decimal_negative": [{"locale": null, "scope": null, "data": {"amount": "-273", "unit": "CELSIUS"}}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"The a_metric_without_decimal_negative attribute does not exist in your PIM. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedUpdateUncategorizedListOfProductsWithOnlyViewableAttribute()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();

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
    {"uuid": "{$uuid->toString()}", "values": {"a_number_integer": [{"locale": null, "scope": null, "data": "1"}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsOnNotGrantedLocale()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();
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
    {"uuid": "{$uuid->toString()}", "values": {"a_localized_and_scopable_text_area": [{"locale": "de_DE", "scope": "ecommerce", "data": "May the patch works without permission!"}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"Attribute \"a_localized_and_scopable_text_area\" expects an existing and activated locale, \"de_DE\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsOnOnlyViewableLocale()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();
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
    {"uuid": "{$uuid->toString()}", "values": {"a_localized_and_scopable_text_area": [{"locale": "fr_FR", "scope": "ecommerce", "data": "Qui ne tente rien ..."}]}}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsWithNotGrantedCategory()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();
        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryB"]}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":422,"message":"Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateUncategorizedListOfProductsWithViewableCategory()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();
        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryA2"]}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"You should at least keep your product in one category on which you have an own permission."}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsWithEditGrantedCategory()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_without_category');
        $newUuid = Uuid::uuid4();
        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryA"]}
    {"uuid": "{$newUuid->toString()}", "values": {"sku": [{"locale": null, "scope": null, "data": "toto"}]}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":403,"message":"You should at least keep your product in one category on which you have an own permission."}
{"line":2,"uuid":"{$newUuid->toString()}","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToAssociateANotGrantedProductWhichHasAlreadyBeenProcessed()
    {
        $uuid = $this->getProductUuidFromIdentifier('product_not_viewable_by_redactor');

        $uuid2 = $this->createProduct('another_without_category')->getUuid();
        $data = <<<JSON
    {"uuid": "{$uuid->toString()}", "categories": ["categoryA"]}
    {"uuid": "{$uuid2->toString()}", "associations": {"PACK": {"products": ["product_not_viewable_by_redactor"]}}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"uuid":"{$uuid->toString()}","code":404,"message":"Product \"product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."}
{"line":2,"uuid":"{$uuid2->toString()}","code":422,"message":"Property \"associations\" expects an array with valid data, association format is not valid for the association type \"PACK\", \"product_uuids\" expects an array of valid uuids.. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    protected function executeAndCheckStreamRequest($data)
    {
        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products-uuid', [], [], [], $data, true, 'mary', 'mary');

        $httpResponse = $response['http_response'];
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());

        return $response;
    }
}
