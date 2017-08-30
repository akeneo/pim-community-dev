<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListPublishedProductIntegration extends AbstractPublishedProductTestCase
{
    public function testNotFoundChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=not_found');
        $this->assert($client, 'Scope "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?pagination_type=unknown');
        $this->assert($client, 'Pagination type does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?locales=not_found');
        $this->assert($client, 'Locale "not_found" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?locales=not_found,jambon');
        $this->assert($client, 'Locales "not_found, jambon" do not exist or are not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testInactiveLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce&locales=de_DE');
        $this->assert($client, 'Locale "de_DE" is not activated for the scope "ecommerce".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testInactiveLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce&locales=de_DE,fr_FR');
        $this->assert($client, 'Locales "de_DE, fr_FR" are not activated for the scope "ecommerce".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?attributes=not_found');
        $this->assert($client, 'Attribute "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testNotFoundAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?attributes=not_found,jambon');
        $this->assert($client, 'Attributes "not_found, jambon" do not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPaginationWherePageIsNotAnInteger()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?page=string');
        $this->assert($client, '"string" is not a valid page number.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPaginationWhereLimitIsTooBig()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/published-products?limit=101');
        $this->assert($client, 'You cannot request more than 100 items.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchFormatIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search=string');
        $this->assert($client, 'Search query parameter should be valid JSON.', Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/api/rest/v1/published-products?search={"a_localized_and_scopable_text_area":{"key"}}');
        $this->assert($client, 'Search query parameter should be valid JSON.', Response::HTTP_BAD_REQUEST);

        $client->request('GET', '/api/rest/v1/published-products?search={"a_localized_and_scopable_text_area":{"operator": "="}}');
        $this->assert($client, 'Structure of filter "a_localized_and_scopable_text_area" should respect this structure: {"a_localized_and_scopable_text_area":[{"operator": "my_operator", "value": "my_value"}]}', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_localized_and_scopable_text_area":[{"value":"text"}]}');
        $this->assert($client, 'Operator is missing for the property "a_localized_and_scopable_text_area".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithWrongOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_localized_and_scopable_text_area":[{"operator":"BETWEEN", "value":"text"}]}');
        $this->assert($client, 'Filter on property "a_localized_and_scopable_text_area" is not supported or does not support operator "BETWEEN"', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_localizable_image" expects a locale, none given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100}]}');
        $this->assert($client, 'Property "completeness" expects an array with the key "locales".', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithLocalesAsAString()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100, "locales":"fr_FR"}]}');
        $this->assert($client, 'Property "completeness" expects an array of arrays as data.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects a scope, none given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithNotFoundLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            '/api/rest/v1/published-products?search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text", "locale":"not_found"}]}'
        );
        $this->assert(
            $client,
            'Locale "not_found" does not exist or is not activated.',
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $client->request(
            'GET',
            '/api/rest/v1/published-products?search_locale=not_found&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}'
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

        $client->request('GET', '/api/rest/v1/published-products?search_locale=ar_TN&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "ar_TN" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithNotFoundScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text", "scope":"not_found"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.', Response::HTTP_UNPROCESSABLE_ENTITY);

        $client->request('GET', '/api/rest/v1/published-products?search_scope=not_found&search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchWithObjectNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"categories":[{"operator":"IN","value":["not_found"]}]}');
        $this->assert($client, 'Category "not_found" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchIsNotAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search="not_an_array"');
        $this->assert($client, 'Search query parameter has to be an array, "string" given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testOperatorIsAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/published-products?search={"a_text":[{"operator":["="], "value":"text"}]}');
        $this->assert($client, 'Operator has to be a string, "array" given.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_metric_without_decimal_negative');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testSearchPublishedProductAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?search={"a_metric_without_decimal_negative":[{"operator":"EMPTY"}]}');
        $this->assert($client, 'Filter on property "a_metric_without_decimal_negative" is not supported or does not support operator "EMPTY"', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductAttributesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductOneAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?attributes=a_multi_select,a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attributes "a_multi_select, a_metric_without_decimal_negative" do not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?locales=de_DE');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductOneLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/published-products?locales=de_DE,en_US');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchCategoryNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search={"categories":[{"operator":"IN","value":["categoryB"]}]}');
        $this->assert($client, 'Category "categoryB" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchCategoriesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search={"categories":[{"operator":"IN","value":["categoryB", "categoryC"]}]}');
        $this->assert($client, 'Categories "categoryB, categoryC" do not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchOneCategoryNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search={"categories":[{"operator":"IN","value":["categoryB", "categoryA2"]}]}');
        $this->assert($client, 'Category "categoryB" does not exist.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search_locale=de_DE&search={"a_localized_and_scopable_text_area":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchOneLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search_locale=de_DE,fr_FR&search={"a_localized_and_scopable_text_area":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchOneLocalesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search={"completeness":[{"operator":"GREATER OR EQUALS THAN ON ALL LOCALES","value":40,"locales":["de_DE"],"scope":"ecommerce"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testPublishedProductSearchLocalesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/published-products?search={"completeness":[{"operator":"GREATER OR EQUALS THAN ON ALL LOCALES","value":40,"locales":["fr_FR","de_DE"],"scope":"ecommerce"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /**
     * @param Client $client
     * @param string $message
     * @param int    $code
     */
    private function assert(Client $client, string $message, int $code)
    {
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', $code, addslashes($message));

        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($expected, $response->getContent());
    }
}
