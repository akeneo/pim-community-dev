<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Security;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AuthorizationIntegration extends ApiTestCase
{
    public function testAccessGranted()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories/master');

        $standardCategory = [
            'code'   => 'master',
            'parent' => null,
            'labels' => []
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardCategory, json_decode($response->getContent(), true));
    }

    public function testAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', 'api/rest/v1/categories/master');

        $expectedResponse = [
            'code'    => 403,
            'message' => 'Access Denied.',
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponse, json_decode($response->getContent(), true));
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
