<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternaApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class InternalApiEndToEnd extends ApiTestCase
{
    /**
     * Should be an integration test.
     */
    public function testLocalesPermissionsEndpoint()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'julia',
            'PHP_AUTH_PW' => 'julia',
        ]);
        $client->request('GET', '/permissions/locales/rest');

        $expectedResponse = <<<JSON
[
    {
        "code": "de_DE",
        "view": true,
        "edit": true
    },
    {
        "code": "en_US",
        "view": true,
        "edit": true
    },
    {
        "code": "fr_FR",
        "view": true,
        "edit": true
    },
    {
        "code": "zh_CN",
        "view": true,
        "edit": true
    }
]
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
