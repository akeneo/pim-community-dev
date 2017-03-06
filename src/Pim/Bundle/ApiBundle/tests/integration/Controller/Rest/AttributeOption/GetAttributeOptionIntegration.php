<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\AttributeOption;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAttributeOptionIntegration extends ApiTestCase
{
    public function testGetAnAttributeOption()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_multi_select/options/optionA');

        $standardAttributeOption = [
            'code'       => 'optionA',
            'attribute'  => 'a_multi_select',
            'sort_order' => 10,
            'labels'     => [
                'en_US' => 'Option A',
            ],
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardAttributeOption, json_decode($response->getContent(), true));
    }

    public function testNotFoundAnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/not_found/options/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Attribute "not_found" does not exist.', $content['message']);
    }

    public function testNotSupportedOptionsAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/sku/options/sku');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame(
            'Attribute "sku" does not support options. Only attributes of type "pim_catalog_simpleselect", "pim_catalog_multiselect" support options.',
            $content['message']
        );
    }

    public function testNotExistingOptionsAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/a_multi_select/options/not_existing_option');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame(
            'Attribute option "not_existing_option" does not exist or is not an option of the attribute "a_multi_select".',
            $content['message']
        );
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
