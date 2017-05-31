<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociationTypeAuthorizationIntegration extends ApiTestCase
{
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $client->request('GET', '/api/rest/v1/association-types');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForListingAssociationTypes()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/association-types');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForListingAssociationTypes()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/association-types');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list association types."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testAccessGrantedForGettingAnAssociationType()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/association-types/X_SELL');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAccessDeniedForGettingAnAssociationType()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/association-types/X_SELL');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list association types."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()]
        );
    }
}
