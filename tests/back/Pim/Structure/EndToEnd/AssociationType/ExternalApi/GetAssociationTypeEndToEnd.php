<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AssociationType\ExternalApi;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAssociationTypeEndToEnd extends ApiTestCase
{
    public function testGetAnAssociationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types/X_SELL');

        $standardAssociationType = <<<JSON
{
    "code": "X_SELL",
    "labels": {
        "en_US": "Cross sell",
        "fr_FR": "Vente croisée"
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardAssociationType, $response->getContent());
    }

    public function testNotFoundAnAssociationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/association-types/not_found');

        $response = $client->getResponse();

        $expected = <<<JSON
{
	"code": 404,
	"message": "Association type \"not_found\" does not exist."
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
