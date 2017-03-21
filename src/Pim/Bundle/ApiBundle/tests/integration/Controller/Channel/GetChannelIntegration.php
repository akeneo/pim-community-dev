<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Channel;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetChannelIntegration extends ApiTestCase
{
    public function testGetAChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels/ecommerce');

        $apiChannel = <<<JSON
{
    "code": "ecommerce",
    "currencies": ["USD", "EUR"],
    "locales": ["en_US"],
    "category_tree": "master",
    "conversion_units": {},
    "labels": {
        "en_US" : "Ecommerce",
        "fr_FR" : "Ecommerce"
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($apiChannel, $response->getContent());
    }

    public function testNotFoundAChannel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/channels/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Channel "not_found" does not exist.', $content['message']);
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
}
