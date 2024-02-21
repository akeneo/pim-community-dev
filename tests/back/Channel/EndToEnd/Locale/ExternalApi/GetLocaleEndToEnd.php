<?php

namespace AkeneoTest\Channel\EndToEnd\Locale\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetLocaleEndToEnd extends ApiTestCase
{
    public function testGetAnActivatedLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales/en_US');

        $apiLocale = [
            'code'    => 'en_US',
            'enabled' => true,
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($apiLocale, json_decode($response->getContent(), true));
    }

    public function testGetADisabledLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales/af_ZA');

        $apiLocale = [
            'code'    => 'af_ZA',
            'enabled' => false,
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($apiLocale, json_decode($response->getContent(), true));
    }

    public function testNotFoundALocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/locales/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Locale "not_found" does not exist.', $content['message']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
