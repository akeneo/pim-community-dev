<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

class ErrorListProductIntegration extends TestCase
{
    public function testNotFoundChannel()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=not_found');
        $this->assert($client, 'Channel "not_found" does not exist.');
    }

    public function testNotFoundLocale()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found');
        $this->assert($client, 'Locale "not_found" does not exist.');
    }

    public function testNotFoundLocales()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?locales=not_found,jambon');
        $this->assert($client, 'Locales "not_found, jambon" do not exist.');
    }

    public function testInactiveLocale()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce&locales=de_DE');
        $this->assert($client, 'Locale "de_DE" is not activated for the channel "ecommerce".');
    }

    public function testInactiveLocales()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?channel=ecommerce&locales=de_DE,fr_FR');
        $this->assert($client, 'Locales "de_DE, fr_FR" are not activated for the channel "ecommerce".');
    }

    public function testNotFoundAttribute()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found');
        $this->assert($client, 'Attribute "not_found" does not exist.');
    }

    public function testNotFoundAttributes()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?attributes=not_found,jambon');
        $this->assert($client, 'Attributes "not_found, jambon" do not exist.');
    }

    public function testPaginationWherePageIsNotAnInteger()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?page=string');
        $this->assert($client, '"string" is not a valid page number.');
    }

    public function testPaginationWhereLimitIsTooBig()
    {
        $client = static::createClient();

        $client->request('GET', 'api/rest/v1/products?limit=101');
        $this->assert($client, 'You cannot request more than 100 items.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
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
