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
            "pim_api_category_partial_update_list": {
                "route": "/api/rest/v1/categories",
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
            "pim_api_family_partial_update_list": {
                "route": "/api/rest/v1/families",
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
            "pim_api_attribute_partial_update": {
                "route": "/api/rest/v1/attributes/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_get": {
                "route": "/api/rest/v1/attributes/{code}",
                "methods": ["GET"]
            },
            "pim_api_attribute_partial_update_list": {
                "route": "/api/rest/v1/attributes",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_option_list": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options",
                "methods": ["GET"]
            },
            "pim_api_attribute_option_create": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options",
                "methods": ["POST"]
            },
            "pim_api_attribute_option_get": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options/{code}",
                "methods": ["GET"]
            },
            "pim_api_attribute_option_partial_update": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_option_partial_update_list": {
                "route": "/api/rest/v1/attributes/{attributeCode}/options",
                "methods": ["PATCH"]
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
            },
            "pim_api_product_partial_update": {
                "route": "/api/rest/v1/products/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_product_partial_update_list": {
                "route": "/api/rest/v1/products",
                "methods": ["PATCH"]
            },
            "pim_api_locale_list": {
                "route": "/api/rest/v1/locales",
                "methods": ["GET"]
            },
            "pim_api_locale_get": {
                "route": "/api/rest/v1/locales/{code}",
                "methods": ["GET"]
            },
            "pim_api_media_file_list": {
                "route": "/api/rest/v1/media-files",
                "methods": ["GET"]
            },
            "pim_api_media_file_download": {
                "route": "/api/rest/v1/media-files/{code}/download",
                "methods": ["GET"]
            },
            "pim_api_media_file_get": {
                "route": "/api/rest/v1/media-files/{code}",
                "methods": ["GET"]
            },
            "pim_api_media_file_create": {
                "route": "/api/rest/v1/media-files",
                "methods": ["POST"]
            },
            "pim_api_attribute_group_list": {
                "route": "/api/rest/v1/attribute-groups",
                "methods": ["GET"]
            },
            "pim_api_attribute_group_get": {
                "route": "/api/rest/v1/attribute-groups/{code}",
                "methods": ["GET"]
            },
            "pim_api_attribute_group_create": {
                "route": "/api/rest/v1/attribute-groups",
                "methods": ["POST"]
            },
            "pim_api_attribute_group_partial_update": {
                "route": "/api/rest/v1/attribute-groups/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_attribute_group_partial_update_list": {
                "route": "/api/rest/v1/attribute-groups",
                "methods": ["PATCH"]
            },
            "pim_api_currency_get": {
                "route": "/api/rest/v1/currencies/{code}",
                "methods": ["GET"]
            },
            "pim_api_currency_list": {
                "route": "/api/rest/v1/currencies",
                "methods": ["GET"]
            },
            "pim_api_measure_family_list": {
                "route": "/api/rest/v1/measure-families",
                "methods": ["GET"]
            },
            "pim_api_measure_family_get": {
                "route": "/api/rest/v1/measure-families/{code}",
                "methods": ["GET"]
            },
            "pim_api_channel_create": {
                "route": "/api/rest/v1/channels",
                "methods": ["POST"]
            },
            "pim_api_channel_partial_update": {
                "route": "/api/rest/v1/channels/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_channel_partial_update_list": {
                "route": "/api/rest/v1/channels",
                "methods": ["PATCH"]
            },
            "pim_api_association_type_get": {
                "route": "/api/rest/v1/association-types/{code}",
                "methods": ["GET"]
            },
            "pim_api_association_type_list": {
                "route": "/api/rest/v1/association-types",
                "methods": ["GET"]
            },
            "pim_api_association_type_create": {
                "route": "/api/rest/v1/association-types",
                "methods": ["POST"]
            },
            "pim_api_association_type_partial_update": {
                "route": "/api/rest/v1/association-types/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_association_type_partial_update_list": {
                "route": "/api/rest/v1/association-types",
                "methods": ["PATCH"]
            },
            "pim_api_product_model_get": {
                "route": "/api/rest/v1/product-models/{code}",
                "methods": ["GET"]
            },
            "pim_api_family_variant_get": {
                "route": "/api/rest/v1/families/{familyCode}/variants/{code}",
                "methods": ["GET"]
            },
            "pim_api_family_variant_list": {
                "route": "/api/rest/v1/families/{familyCode}/variants",
                "methods": ["GET"]
            },
            "pim_api_product_model_list": {
                "route": "/api/rest/v1/product-models",
                "methods": ["GET"]
            },
            "pim_api_product_model_create": {
                "route": "/api/rest/v1/product-models",
                "methods": ["POST"]
            },
            "pim_api_family_variant_create": {
                "route": "/api/rest/v1/families/{familyCode}/variants",
                "methods": ["POST"]
            },
            "pim_api_product_model_partial_update": {
                "route": "/api/rest/v1/product-models/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_product_model_partial_update_list": {
                "route": "/api/rest/v1/product-models",
                "methods": ["PATCH"]
            },
            "pim_api_family_variant_partial_update": {
                "route": "/api/rest/v1/families/{familyCode}/variants/{code}",
                "methods": ["PATCH"]
            },
            "pim_api_family_variant_partial_update_list": {
                "route": "/api/rest/v1/families/{familyCode}/variants",
                "methods": ["PATCH"]
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
        return $this->catalog->useTechnicalCatalog();
    }
}
