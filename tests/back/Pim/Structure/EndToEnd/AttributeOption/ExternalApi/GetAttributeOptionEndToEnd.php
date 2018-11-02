<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\AttributeOption\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAttributeOptionEndToEnd extends ApiTestCase
{
    public function testGetAnAttributeOption()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_multi_select/options/optionA');

        $expectedContent =
<<<JSON
    {
        "code" : "optionA",
        "sort_order" : 10,
        "attribute" : "a_multi_select",
        "labels" : {
          "en_US" : "Option A"
        }
    }
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testNotFoundAnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/not_found/options/not_found');

        $expectedContent =
<<<JSON
    {
      "message" : "Attribute \"not_found\" does not exist.",
      "code" : 404
    }
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testNotSupportedOptionsAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/sku/options/sku');

        $expectedContent =
<<<JSON
    {
      "message" : "Attribute \"sku\" does not support options. Only attributes of type \"pim_catalog_simpleselect\", \"pim_catalog_multiselect\" support options.",
      "code" : 404
    }
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testNotExistingOptionsAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_multi_select/options/not_existing_option');

        $expectedContent =
<<<JSON
    {
      "message" : "Attribute option \"not_existing_option\" does not exist or is not an option of the attribute \"a_multi_select\".",
      "code" : 404
    }
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
