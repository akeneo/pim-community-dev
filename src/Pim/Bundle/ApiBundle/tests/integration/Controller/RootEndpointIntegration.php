<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class RootEndpointIntegration extends ApiTestCase
{
    public function testGetEndpoint()
    {
        $client = static::createClient();
        $client->request('GET', 'api/rest/v1');

        $expected =
<<<JSON
    {
        "host": "http://localhost",
        "authentication": {
            "fos_oauth_server_token": {
                "route": "/api/oauth/v1/token",
                "methods": ["POST"]
            }
        },
        "routes": {
            "pim_api_category_list": {
                "route": "/api/rest/v1/categories",
                "methods": ["GET"]
            },
            "pim_api_category_get": {
                "route": "/api/rest/v1/categories/{code}",
                "methods": ["GET"]
            },
            "pim_api_category_create": {
                "route": "/api/rest/v1/categories",
                "methods": ["POST"]
            },
            "pim_api_category_partial_update": {
                "route": "/api/rest/v1/categories/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_family_list": {
                "route": "/api/rest/v1/families",
                "methods": ["GET"]
            },
            "pim_api_family_get": {
                "route": "/api/rest/v1/families/{code}",
                "methods": ["GET"]
            },
            "pim_api_family_create": {
                "route": "/api/rest/v1/families",
                "methods": ["POST"]
            },
            "pim_api_family_partial_update": {
                "route": "/api/rest/v1/families/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_list": {
                "route": "/api/rest/v1/attributes",
                "methods": ["GET"]
            },
            "pim_api_attribute_create": {
                "route": "/api/rest/v1/attributes",
                "methods": ["POST"]
            },
            "pim_api_attribute_update": {
                "route": "/api/rest/v1/attributes/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_get": {
                "route": "/api/rest/v1/attributes/{code}",
                "methods": ["GET"]
            },
            "pim_api_attribute_option_create": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options",
                "methods": ["POST"]
            },
            "pim_api_attribute_option_get": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options/{optionCode}",
                "methods": ["GET"]
            },
            "pim_api_channel_list": {
                "route": "/api/rest/v1/channels",
                "methods": ["GET"]
            },
            "pim_api_channel_get": {
                "route": "/api/rest/v1/channels/{code}",
                "methods": ["GET"]
            },
            "pim_api_product_list": {
                "route": "/api/rest/v1/products",
                "methods": ["GET"]
            },
            "pim_api_product_get": {
                "route": "/api/rest/v1/products/{code}",
                "methods": ["GET"]
            },
            "pim_api_product_create": {
                "route": "/api/rest/v1/products",
                "methods": ["POST"]
            },
            "pim_api_product_delete": {
                "route": "/api/rest/v1/products/{code}",
                "methods": ["DELETE"]
            }
        }
    }
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
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
