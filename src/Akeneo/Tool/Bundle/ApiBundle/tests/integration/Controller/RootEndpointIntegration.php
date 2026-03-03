<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Controller;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

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

        $this->assertArrayNotHasKey('akeneo_asset_manager_asset_families_rest_connector_get', $payload['routes']);
        $this->assertArrayNotHasKey('akeneo_shared_catalog_product_list_rest', $payload['routes']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
