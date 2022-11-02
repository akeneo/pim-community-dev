<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\EndToEnd\Attribute;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateAttributeEndToEnd extends AbstractAttributeApiTestCase
{
    public function testItUpdatesATableAttribute(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createValidTableAttribute($client);

        $data =
            <<<JSON
    {
        "code":"a_table_attribute",
        "type":"pim_catalog_table",
        "group":"attributeGroupA",
        "table_configuration": [
            {
                "code": "INGredients",
                "data_type": "select",
                "labels": {
                    "fr_FR":"Ingraydients",
                    "de_DE":"Zutat"
                }
            },
            {
                "code": "quantity",
                "data_type": "text",
                "is_required_for_completeness": true
            },
            {
                "code": "manufacturing_time",
                "data_type": "measurement",
                "measurement_family_code": "duration",
                "measurement_default_unit_code": "minute"
            }
        ]
    }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', 'api/rest/v1/attributes/a_table_attribute', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes/a_table_attribute');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $decoded = \json_decode($response->getContent(), true);
        self::assertArrayHasKey('table_configuration', $decoded);
        self::assertEqualsCanonicalizing([
            "fr_FR" => "Ingraydients",
            "de_DE" => "Zutat",
        ], $decoded['table_configuration'][0]['labels']);
        self::assertTrue($decoded['table_configuration'][1]['is_required_for_completeness']);

        self::assertSame('minute', $decoded['table_configuration'][2]['measurement_default_unit_code']);
    }

    public function testItUpdatesATableConfigurationWhenChangingAColumnDatatype(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createValidTableAttribute($client);

        $data =
            <<<JSON
    {
        "code":"a_table_attribute",
        "type":"pim_catalog_table",
        "group":"attributeGroupA",
        "table_configuration": [
            {
                "code": "ingredients",
                "data_type": "select",
                "labels": {
                    "en_US":"Ingredients",
                    "fr_FR":"Ingrédients"
                },
                "options": [{"code": "sugar", "labels": {"en_US": "Sugar", "fr_FR": "Sucre"}}]
            },
            {
                "code": "quantity",
                "data_type": "number",
                "validations": {
                    "min": 0,
                    "decimals_allowed": true
                }
            }
        ]
    }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', 'api/rest/v1/attributes/a_table_attribute', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes/a_table_attribute');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $decoded = \json_decode($response->getContent(), true);
        self::assertArrayHasKey('table_configuration', $decoded);
        self::assertSame('quantity', $decoded['table_configuration'][1]['code'] ?? null);
        self::assertSame('number', $decoded['table_configuration'][1]['data_type'] ?? null);
        self::assertEqualsCanonicalizing(
            ['min' => 0, 'decimals_allowed' => true],
            $decoded['table_configuration'][1]['validations'] ?? null
        );
    }
}
