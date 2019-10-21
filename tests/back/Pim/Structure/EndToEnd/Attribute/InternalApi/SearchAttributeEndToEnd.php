<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group ce
 */
class SearchAttributeEndToEnd extends ApiTestCase
{
    const ENDPOINT_URL = 'rest/attribute/';

    /**
     * Search attributes by identifiers with query param '?identifiers='.
     */
    public function testByIdentifiers(): void
    {
        $params = [
            'identifiers' => 'sku,a_metric'
        ];

        $standardizedAttributes = $this->getStandardizedAttributes();
        $expected = <<<JSON
        [
            {$standardizedAttributes['sku']},
            {$standardizedAttributes['a_metric']}
        ]
JSON;

        // GET
        $client = self::createSearchAttributeClient();
        $client->request('GET', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

        // POST
        $client = self::createSearchAttributeClient();
        $client->request('POST', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * Search attributes by identifiers with query param '?options[identifiers][]='.
     */
    public function testByIdentifiersWithOptionsParam(): void
    {
        $params = [
            'options' => [
                'identifiers' => ['sku', 'a_metric']
            ]
        ];

        $standardizedAttributes = $this->getStandardizedAttributes();
        $expected = <<<JSON
        [
            {$standardizedAttributes['sku']},
            {$standardizedAttributes['a_metric']}
        ]
JSON;

        // GET
        $client = self::createSearchAttributeClient();
        $client->request('GET', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

        // POST
        $client = self::createSearchAttributeClient();
        $client->request('POST', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * Search attributes by types with query param '?types='.
     */
    public function testByTypes(): void
    {
        $params = [
            'types' => 'pim_catalog_identifier'
        ];

        $standardizedAttributes = $this->getStandardizedAttributes();
        $expected = <<<JSON
        [
            {$standardizedAttributes['sku']}
        ]
JSON;

        // GET
        $client = self::createSearchAttributeClient();
        $client->request('GET', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());

        // POST
        $client = self::createSearchAttributeClient();
        $client->request('POST', self::ENDPOINT_URL, $params);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private static function createSearchAttributeClient(): Client
    {
        return self::createClient([], [
            'PHP_AUTH_USER' => self::PASSWORD,
            'PHP_AUTH_PW'   => self::USERNAME,
        ]);
    }

    private function getStandardizedAttributes(): array
    {
        $standardizedAttributes = [];

        $attribute = $this->get('pim_api.repository.attribute')->findOneByIdentifier('sku');

        $standardizedAttributes['sku'] = <<<JSON
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
            "labels": [],
            "auto_option_sorting": null,
            "empty_value": null,
            "field_type": "akeneo-text-field",
            "filter_types": {
                "product-export-builder": "akeneo-attribute-identifier-filter"
            },
            "is_locale_specific": false,
            "meta": {
                "id": {$attribute->getId()}
            }
        }
JSON;

        $attribute = $this->get('pim_api.repository.attribute')->findOneByIdentifier('a_metric');

        $standardizedAttributes['a_metric'] = <<<JSON
        {
            "code": "a_metric",
            "type": "pim_catalog_metric",
            "group": "attributeGroupB",
            "unique": false,
            "useable_as_grid_filter": false,
            "allowed_extensions": [],
            "metric_family": "Power",
            "default_metric_unit": "KILOWATT",
            "reference_data_name": null,
            "available_locales": [],
            "max_characters": null,
            "validation_rule": null,
            "validation_regexp": null,
            "wysiwyg_enabled": null,
            "number_min": null,
            "number_max": null,
            "decimals_allowed": true,
            "negative_allowed": false,
            "date_min": null,
            "date_max": null,
            "max_file_size": null,
            "minimum_input_length": null,
            "sort_order": 0,
            "localizable": false,
            "scopable": false,
            "labels": [],
            "auto_option_sorting": null,
            "empty_value": {
                "amount": null,
                "unit": "KILOWATT"
            },
            "field_type": "akeneo-metric-field",
            "filter_types": {
                "product-export-builder": "akeneo-attribute-metric-filter"
            },
            "is_locale_specific": false,
            "meta": {
                "id": {$attribute->getId()}
            }
        }
JSON;

        return $standardizedAttributes;
    }
}
