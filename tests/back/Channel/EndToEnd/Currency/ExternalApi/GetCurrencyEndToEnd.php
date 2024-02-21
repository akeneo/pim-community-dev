<?php

namespace AkeneoTest\Channel\EndToEnd\Currency\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetCurrencyEndToEnd extends ApiTestCase
{
    public function testGetACurrency()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies/eur');

        $standardCurrency = <<<JSON
{
    "code": "EUR",
    "enabled": true
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardCurrency, $response->getContent());
    }

    public function testNotFoundACurrency()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/currencies/not_found');

        $response = $client->getResponse();

        $expected = <<<JSON
{
	"code": 404,
	"message": "Currency \"not_found\" does not exist."
}
JSON;

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
