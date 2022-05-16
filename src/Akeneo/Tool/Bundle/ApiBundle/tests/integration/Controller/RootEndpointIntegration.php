<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class RootEndpointIntegration extends ApiTestCase
{
    public function testGetEndpoint()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', 'api/rest/v1');

        $response = $client->getResponse();
        $payload = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals([
            'route' => '/api/oauth/v1/token',
            'methods' => ['POST'],
        ], $payload['authentication']['fos_oauth_server_token']);
        $this->assertEquals([
            'route' => '/api/rest/v1/products',
            'methods' => ['GET'],
        ], $payload['routes']['pim_api_product_list']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
