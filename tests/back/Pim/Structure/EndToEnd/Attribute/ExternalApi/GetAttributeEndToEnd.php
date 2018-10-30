<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class GetAttributeEndToEnd extends ApiTestCase
{
    public function testGetAnAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/attributes/sku');

        $standardAttribute = <<<JSON
{
    "code": "sku",
    "type": "pim_catalog_identifier",
    "group": "attributeGroupA",
    "unique": true,
    "useable_as_grid_filter": true,
    "allowed_extensions": [],
    "metric_family": null,
    "default_metric_unit": null,
    "reference_data_name": null,
    "available_locales": [],
    "max_characters": null,
    "validation_rule": null,
    "validation_regexp": null,
    "wysiwyg_enabled": null,
    "number_min": null,
    "number_max": null,
    "decimals_allowed": null,
    "negative_allowed": null,
    "date_min": null,
    "date_max": null,
    "max_file_size": null,
    "minimum_input_length": null,
    "sort_order": 0,
    "localizable": false,
    "scopable": false,
    "labels": {},
    "auto_option_sorting": null
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($standardAttribute, $response->getContent());
    }

    public function testNotFoundAnAttribute()
    {
        $client = $this->createAuthenticatedClient();

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
        return $this->catalog->useTechnicalCatalog();
    }
}
