<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Update;

use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateListOfProductsIntegration extends AbstractProductTestCase
{
    public function testFailedToUpdateListOfProductsIfHeaderIsWrong()
    {
        $data = <<<JSON
[
    {
        "identifier": "not_existing_product",
        "values": {
            "a_metric_without_decimal_negative": [
                {
                    "data": [
                        {
                            "amount": "-273",
                            "unit": "CELSIUS"
                        }
                    ],
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
        $client->request('PATCH', 'api/rest/v1/products', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedMessage, $response->getContent());
    }

    public function testFailedToUpdateListOfNotExistingProductsWithNotGrantedAttribute()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "values": {"a_metric_without_decimal_negative": [{"data": [{"amount": "-273", "unit": "CELSIUS"}], "locale": null, "scope": null}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":422,"message":"Property \"a_metric_without_decimal_negative\" does not exist. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateAListOfNotExistingProductsWithOnlyViewGrantedAttribute()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "values": {"a_number_integer": [{"data": 42, "locale": null, "scope": null}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":403,"message":"Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfNotExistingProductsWithNotGrantedCategory()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "categories": ["categoryB"]}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":422,"message":"Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testSuccessfullyUpdateListOfNotExistingProductsWithAnOnlyViewableCategory()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "categories": ["categoryA2"]}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":201}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('not_existing_product');
        $this->assertSame(['categoryA2'], $product->getCategoryCodes());
    }

    public function testFailedToUpdateNotExistingProductsWithNotGrantedLocale()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "values": {"a_localized_and_scopable_text_area": [{"data": "Lorem ipsum dolor sit amet.", "locale": "de_DE", "scope": "tablet"}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":422,"message":"Attribute \"a_localized_and_scopable_text_area\" expects an existing and activated locale, \"de_DE\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfNotExistingProductsWithOnlyViewGrantedLocale()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "values": {"a_localized_and_scopable_text_area": [{"data": "Lorem ipsum dolor sit amet.", "locale": "fr_FR", "scope": "tablet"}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateAssociationOfListOfNotExistingProductsWithNotGrantedCategory()
    {
        $data = <<<JSON
    {"identifier": "not_existing_product", "associations": {"PACK": {"products": ["product_not_viewable_by_redactor"]}}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"not_existing_product","status_code":403,"message":"You cannot associate a product on which you have not a view permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateListOfProductsNotViewableByUser()
    {
        $data = <<<JSON
    {"identifier": "product_not_viewable_by_redactor", "values": {"a_localized_and_scopable_text_area": [{"data": "Awesome Data !", "locale": "en_US", "scope": "ecommerce"}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_not_viewable_by_redactor","status_code":403,"message":"You can neither view, nor update, nor delete the product \"product_not_viewable_by_redactor\", as it is only categorized in categories on which you do not have a view permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testSuccessfullyUpdateAListOfProducts()
    {
        $data = <<<JSON
    {"identifier": "product_without_category", "values": {"a_text": [{"data": "the text", "locale": null, "scope": null}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":204}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_without_category');
        $this->assertSame('the text', $product->getValue('a_text')->getData());
    }

    public function testSuccessfullyUpdateAGrantedLocalizedValueOnUncategorizedListOfProductsEvenIfANotGrantedLocaleIsFilled()
    {
        $updatedData = [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['locale' => 'de_DE', 'scope' => 'ecommerce', 'data' => 'DE ecommerce']
                ]
            ]
        ];
        $this->updateProduct($updatedData, 'product_without_category');

        $data = <<<JSON
    {"identifier": "product_without_category", "values": {"a_localized_and_scopable_text_area": [{"locale": "en_US", "scope": "ecommerce", "data": "Awesome Data !"}]}}
    {"identifier": "toto"}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $expectedResponse = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":204}
{"line":2,"identifier":"toto","status_code":201}
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

        $data = <<<JSON
    {"identifier": "product_without_category", "values": {"a_metric_without_decimal_negative": [{"locale": null, "scope": null, "data": {"amount": "-273", "unit": "CELSIUS"}}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":422,"message":"Property \"a_metric_without_decimal_negative\" does not exist. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedUpdateUncategorizedListOfProductsWithOnlyViewableAttribute()
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
    {"identifier": "product_without_category", "values": {"a_number_integer": [{"locale": null, "scope": null, "data": "1"}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":403,"message":"Attribute \"a_number_integer\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsOnNotGrantedLocale()
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
    {"identifier": "product_without_category", "values": {"a_localized_and_scopable_text_area": [{"locale": "de_DE", "scope": "ecommerce", "data": "May the patch works without permission!"}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":422,"message":"Attribute \"a_localized_and_scopable_text_area\" expects an existing and activated locale, \"de_DE\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsOnOnlyViewableLocale()
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
    {"identifier": "product_without_category", "values": {"a_localized_and_scopable_text_area": [{"locale": "fr_FR", "scope": "ecommerce", "data": "Qui ne tente rien ..."}]}}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":403,"message":"You only have a view permission on the locale \"fr_FR\"."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsWithNotGrantedCategory()
    {
        $data = <<<JSON
    {"identifier": "product_without_category", "categories": ["categoryB"]}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":422,"message":"Property \"categories\" expects a valid category code. The category does not exist, \"categoryB\" given. Check the expected format on the API documentation.","_links":{"documentation":{"href":"http:\/\/api.akeneo.com\/api-reference.html#patch_products__code_"}}}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testUpdateUncategorizedListOfProductsWithViewableCategory()
    {
        $data = <<<JSON
    {"identifier": "product_without_category", "categories": ["categoryA2"]}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":403,"message":"You should at least keep your product in one category on which you have an own permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    public function testFailedToUpdateUncategorizedListOfProductsWithEditGrantedCategory()
    {
        $data = <<<JSON
    {"identifier": "product_without_category", "categories": ["categoryA"]}
    {"identifier": "toto"}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_without_category","status_code":403,"message":"You should at least keep your product in one category on which you have an own permission."}
{"line":2,"identifier":"toto","status_code":201}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }

    /**
     * Failed see https://akeneo.atlassian.net/browse/API-352
    public function testFailedToAssociateANotGrantedProductWhichHasAlreadyBeenProcessed()
    {
        $this->createProduct('another_without_category');
        $data = <<<JSON
    {"identifier": "product_not_viewable_by_redactor", "categories": ["categoryA"]}
    {"identifier": "another_without_category", "associations": {"PACK": {"products": ["product_not_viewable_by_redactor"]}}}
JSON;
        $expectedContent = <<<JSON
{"line":1,"identifier":"product_not_viewable_by_redactor","status_code":403,"message":"You can neither view, nor update, nor delete the product \"product_not_viewable_by_redactor\", as it is only categorized in categories on which you do not have a view permission."}
{"line":2,"identifier":"another_without_category","status_code":403, "message":"You cannot associate a product on which you have not a view permission."}
JSON;
        $response = $this->executeAndCheckStreamRequest($data);

        $this->assertSame($expectedContent, $response['content']);
    }
    */

    /**
     * @param mixed $data
     *
     * @return array
     */
    protected function executeAndCheckStreamRequest($data)
    {
        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/products', [], [], [], $data, true, 'mary', 'mary');

        $httpResponse = $response['http_response'];
        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());

        return $response;
    }
}
