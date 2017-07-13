<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductWithPermissionsIntegration extends AbstractProductTestCase
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
        $this->assert($client, 'Locale "de_DE" does not exist.');
    }

    public function testProductOneLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', 'api/rest/v1/products?locales=de_DE,en_US');
        $this->assert($client, 'Locale "de_DE" does not exist.');
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
        $this->assert($client, 'Locale "de_DE" does not exist.');
    }

    public function testProductSearchOneLocaleNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/products?search_locale=de_DE,fr_FR&search={"a_localized_and_scopable_text_area":[{"operator":"CONTAINS", "value":"text"}]}');
        $this->assert($client, 'Locale "de_DE" does not exist.');
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
}
