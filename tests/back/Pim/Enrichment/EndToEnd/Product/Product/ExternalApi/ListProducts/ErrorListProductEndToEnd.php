<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\ListProducts;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductEndToEnd extends AbstractProductTestCase
{
    public function testNotFoundChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=not_found');
        $this->assert($client, 'Scope "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?pagination_type=unknown');
        $this->assert($client, 'Pagination type does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found');
        $this->assert($client, 'Locale "not_found" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found, jambon');
        $this->assert($client, 'Locales "not_found, jambon" do not exist or are not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testInactiveLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=de_DE');
        $this->assert($client, 'Locale "de_DE" is not activated for the scope "ecommerce".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testInactiveLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=de_DE, fr_FR');
        $this->assert($client, 'Locales "de_DE, fr_FR" are not activated for the scope "ecommerce".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found');
        $this->assert($client, 'Attribute "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found,jambon');
        $this->assert($client, 'Attributes "not_found, jambon" do not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPaginationWherePageIsNotAnInteger()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?page=string');
        $this->assert($client, '"string" is not a valid page number.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPaginationWhereLimitIsTooBig()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?limit=101');
        $this->assert($client, 'You cannot request more than 100 items.',Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchFormatIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search=string');
        $this->assert($client, 'Search query parameter should be valid JSON.', Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":{"key"}}');
        $this->assert($client, 'Search query parameter should be valid JSON.', Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":{"operator": "="}}');
        $this->assert($client, 'Structure of filter "a_localized_and_scopable_text_area" should respect this structure: {"a_localized_and_scopable_text_area":[{"operator": "my_operator", "value": "my_value"}]}', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":[{"value":"text"}]}');
        $this->assert($client, 'Operator is missing for the property "a_localized_and_scopable_text_area".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithWrongOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":[{"operator":"BETWEEN", "value":"text"}]}');
        $this->assert($client, 'Filter on property "a_localized_and_scopable_text_area" is not supported or does not support operator "BETWEEN"', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_localizable_image" expects a locale, none given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100}]}');
        $this->assert($client, 'Property "completeness" expects an array with the key "locales".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithLocalesAsAString()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100, "locales":"fr_FR"}]}');
        $this->assert($client, 'Property "completeness" expects an array with the key "locales".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithEmptyLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100, "locales":""}]}');
        $this->assert($client, 'Property "completeness" expects an array with the key "locales".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects a scope, none given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithNotFoundLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/rest/v1/products?search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text", "locale":"not_found"}]}'
        );
        $this->assert(
            $client,
            'Locale "not_found" does not exist or is not activated.',
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $client->request(
            'GET',
            '/api/rest/v1/products?search_locale=not_found&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}'
        );
        $this->assert(
            $client,
            'Locale "not_found" does not exist or is not activated.',
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function testSearchWithInactivatedLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search_locale=zh_HK&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "zh_HK" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithNotFoundScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text", "scope":"not_found"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.', Response::HTTP_UNPROCESSABLE_ENTITY);

        $client->request('GET', '/api/rest/v1/products?search_scope=not_found&search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithObjectNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"categories":[{"operator":"IN","value":["not_found"]}]}');
        $this->assert($client, 'Category "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testOperatorIsAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_text":[{"operator":["="], "value":"text"}]}');
        $this->assert($client, 'Operator has to be a string, "array" given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchProductAttributeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?search={"wrong_attribute":[{"operator":"EMPTY"}]}');
        $this->assert($client, 'Filter on property "wrong_attribute" is not supported or does not support operator "EMPTY"', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testMaxPageWithOffsetPaginationType()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?page=101&limit=100');

        $message = addslashes('You have reached the maximum number of pages you can retrieve with the "page" pagination type. Please use the search after pagination type instead');
        $expected = <<<JSON
{
    "code":422,
    "message":"${message}",
    "_links":{
        "documentation":{
            "href": "http:\/\/api.akeneo.com\/documentation\/pagination.html#search-after-type"
        }
    }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $client->getResponse()->getContent());
    }

    /**
     * @param Client $client
     * @param string $message
     * @param int    $code
     */
    private function assert(Client $client, $message, int $code)
    {
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', $code, addslashes($message));

        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
