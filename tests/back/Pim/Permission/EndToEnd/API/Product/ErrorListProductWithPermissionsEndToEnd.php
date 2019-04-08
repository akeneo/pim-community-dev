<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductWithPermissionsEndToEnd extends AbstractProductTestCase
{
    public function testProductAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?attributes=a_metric_without_decimal_negative');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.');
    }

    public function testProductAttributesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?attributes=a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attribute "a_metric_without_decimal_negative" does not exist.');
    }

    public function testProductOneAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?attributes=a_multi_select,a_metric_without_decimal_negative,a_localized_and_scopable_text_area');
        $this->assert($client, 'Attributes "a_multi_select, a_metric_without_decimal_negative" do not exist.');
    }

    public function testProductLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=de_DE');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.');
    }

    public function testProductOneLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=de_DE,en_US');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.');
    }

    public function testProductSearchCategoryNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search={"categories":[{"operator":"IN","value":["categoryB"]}]}');
        $this->assert($client, 'Category "categoryB" does not exist.');
    }

    public function testProductSearchCategoriesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search={"categories":[{"operator":"IN","value":["categoryB", "categoryC"]}]}');
        $this->assert($client, 'Categories "categoryB, categoryC" do not exist.');
    }

    public function testProductSearchOneCategoryNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search={"categories":[{"operator":"IN","value":["categoryB", "categoryA2"]}]}');
        $this->assert($client, 'Category "categoryB" does not exist.');
    }

    public function testProductSearchLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search_locale=de_DE&search={"a_localized_and_scopable_text_area":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.');
    }

    public function testProductSearchOneLocalesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER OR EQUALS THAN ON ALL LOCALES","value":40,"locales":["de_DE"],"scope":"ecommerce"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.');
    }

    public function testProductSearchLocalesNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search={"completeness":[{"operator":"GREATER OR EQUALS THAN ON ALL LOCALES","value":40,"locales":["fr_FR","de_DE"],"scope":"ecommerce"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist or is not activated.');
    }

    public function testSearchProductAttributeNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?search={"a_metric_without_decimal_negative":[{"operator":"EMPTY"}]}');
        $this->assert($client, 'Filter on property "a_metric_without_decimal_negative" is not supported or does not support operator "EMPTY"');
    }

    /**
     * @param Client $client
     * @param string $message
     */
    private function assert(Client $client, $message)
    {
        $response = $client->getResponse();

        $expected = sprintf('{"code":%d,"message":"%s"}', Response::HTTP_UNPROCESSABLE_ENTITY, addslashes($message));

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame($expected, $response->getContent());
    }
}
