<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Rest\Attribute;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAttributeIntegration extends ApiTestCase
{
    public function testGetAnAttribute()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/attributes/sku');

        $standardAttribute = [
            'code'                   => 'sku',
            'type'                   => 'pim_catalog_identifier',
            'group'                  => 'attributeGroupA',
            'unique'                 => true,
            'useable_as_grid_filter' => true,
            'allowed_extensions'     => [],
            'metric_family'          => null,
            'default_metric_unit'    => null,
            'reference_data_name'    => null,
            'available_locales'      => [],
            'max_characters'         => null,
            'validation_rule'        => null,
            'validation_regexp'      => null,
            'wysiwyg_enabled'        => false,
            'number_min'             => null,
            'number_max'             => null,
            'decimals_allowed'       => false,
            'negative_allowed'       => false,
            'date_min'               => null,
            'date_max'               => null,
            'max_file_size'          => null,
            'minimum_input_length'   => 0,
            'sort_order'             => 0,
            'localizable'            => false,
            'scopable'               => false,
            'labels'                 => [],
        ];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame($standardAttribute, json_decode($response->getContent(), true));
    }

    public function testNotFoundAnAttribute()
    {
        $client = $this->createAuthentifiedClient();

        $client->request('GET', 'api/rest/v1/attributes/not_found');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content, 'response contains 2 items');
        $this->assertSame(Response::HTTP_NOT_FOUND, $content['code']);
        $this->assertSame('Attribute "not_found" does not exist.', $content['message']);
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
