<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductIntegration extends AbstractProductTestCase
{
    public function testNotFoundChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=not_found');
        $this->assert($client, 'Scope "not_found" does not exist.');
    }

    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?pagination_type=unknown');
        $this->assert($client, 'Pagination type does not exist.');
    }

    public function testNotFoundLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found');
        $this->assert($client, 'Locale "not_found" does not exist.');
    }

    public function testNotFoundLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found,jambon');
        $this->assert($client, 'Locales "not_found, jambon" do not exist.');
    }

    public function testInactiveLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=de_DE');
        $this->assert($client, 'Locale "de_DE" is not activated for the scope "ecommerce".');
    }

    public function testInactiveLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?scope=ecommerce&locales=de_DE,fr_FR');
        $this->assert($client, 'Locales "de_DE, fr_FR" are not activated for the scope "ecommerce".');
    }

    public function testNotFoundAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found');
        $this->assert($client, 'Attribute "not_found" does not exist.');
    }

    public function testNotFoundAttributes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found,jambon');
        $this->assert($client, 'Attributes "not_found, jambon" do not exist.');
    }

    public function testPaginationWherePageIsNotAnInteger()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?page=string');
        $this->assert($client, '"string" is not a valid page number.');
    }

    public function testPaginationWhereLimitIsTooBig()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products?limit=101');
        $this->assert($client, 'You cannot request more than 100 items.');
    }

    public function testSearchFormatIsNotValid()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search=string');
        $this->assert($client, 'Search query parameter should be valid JSON.');

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":{"key"}}');
        $this->assert($client, 'Search query parameter should be valid JSON.');

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":{"operator": "="}}');
        $this->assert($client, 'Structure of filter "a_localized_and_scopable_text_area" should respect this structure: {"a_localized_and_scopable_text_area":[{"operator": "my_operator", "value": "my_value"}]}');
    }

    public function testSearchWithMissingOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":[{"value":"text"}]}');
        $this->assert($client, 'Operator is missing for the property "a_localized_and_scopable_text_area".');
    }

    public function testSearchWithWrongOperator()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localized_and_scopable_text_area":[{"operator":"BETWEEN", "value":"text"}]}');
        $this->assert($client, 'Filter on property "a_localized_and_scopable_text_area" is not supported or does not support operator "BETWEEN"');
    }

    public function testSearchWithMissingLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_localizable_image" expects a locale, none given.');
    }

    public function testSearchWithMissingLocales()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100}]}');
        $this->assert($client, 'Property "completeness" expects an array with the key "locales" as data.');
    }

    public function testSearchWithLocalesAsAString()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER THAN ON ALL LOCALES", "scope":"ecommerce", "value":100, "locales":"fr_FR"}]}');
        $this->assert($client, 'Property "completeness" expects an array of arrays as data.');
    }

    public function testSearchWithMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects a scope, none given.');
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
            'Attribute "a_localizable_image" expects an existing and activated locale, "not_found" given.'
        );

        $client->request(
            'GET',
            '/api/rest/v1/products?search_locale=not_found&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}'
        );
        $this->assert(
            $client,
            'Attribute "a_localizable_image" expects an existing and activated locale, "not_found" given.'
        );
    }

    public function testSearchWithInactivatedLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search_locale=ar_TN&search={"a_localizable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_localizable_image" expects an existing and activated locale, "ar_TN" given.');
    }

    public function testSearchWithNotFoundScope()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text", "scope":"not_found"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.');

        $client->request('GET', '/api/rest/v1/products?search_scope=not_found&search={"a_scopable_image":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Attribute "a_scopable_image" expects an existing scope, "not_found" given.');
    }

    public function testSearchWithObjectNotFound()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"categories":[{"operator":"IN","value":["not_found"]}]}');
        $this->assert($client, 'Object "category" with code "not_found" does not exist');
    }

    public function testSearchIsNotAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search="not_an_array"');
        $this->assert($client, 'Search query parameter has to be an array, "string" given.');
    }

    public function testOperatorIsAnArray()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/products?search={"a_text":[{"operator":["="], "value":"text"}]}');
        $this->assert($client, 'Operator has to be a string, "array" given.');
    }

    /**
     * @param Client $client
     * @param string $message
     */
    private function assert(Client $client, $message)
    {
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $content['code']);
        $this->assertSame($message, $content['message']);
    }
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
