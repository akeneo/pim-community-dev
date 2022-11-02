<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiRequestQueryParametersSubscriberIntegration extends ApiTestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testErrorIfAnyQueryParameterIsArray(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?array_parameter[]=bad');

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $expected = <<<JSON
            {
                "code": 400,
                "message": "Bracket syntax is not supported in query parameters."
            }
        JSON;

        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
