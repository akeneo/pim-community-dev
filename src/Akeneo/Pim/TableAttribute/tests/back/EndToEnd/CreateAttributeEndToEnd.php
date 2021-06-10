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

namespace Akeneo\Pim\TableAttribute\tests\back\EndToEnd;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class CreateAttributeEndToEnd extends ApiTestCase
{
    public function testItFailsToCreateATableAttributeWithOnlyOneColumn(): void
    {
        $client = $this->createAuthenticatedClient();
        $data =
            <<<JSON
    {
        "code":"a_table_attribute",
        "type":"pim_catalog_table",
        "group":"attributeGroupA",
        "table_configuration": [
            {
                "code": "ingredients",
                "data_type": "select"
            }
        ]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testItCreatesAValidTableAttribute(): void
    {
        $client = $this->createAuthenticatedClient();

        $this->createValidTableAttribute($client);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes/a_table_attribute');
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $decoded = \json_decode($response->getContent(), true);
        self::assertArrayHasKey('table_configuration', $decoded);
        self::assertSame([
            [
                'code' => 'ingredients',
                'data_type' => 'select',
                'labels' => [
                    "en_US" => "Ingredients",
                    "fr_FR" => "Ingrédients",
                ],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => [],
            ]
        ], $decoded['table_configuration']);
    }

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
                "code": "ingredients",
                "data_type": "select",
                "labels": {
                    "fr_FR":"Ingraydients",
                    "de_DE":"Zutat"
                }
            },
            {
                "code": "quantity",
                "data_type": "text"
            }
        ]
    }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->request('PATCH', 'api/rest/v1/attributes/a_table_attribute', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

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
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createValidTableAttribute(Client $client)
    {
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
                }
            },
            {
                "code": "quantity",
                "data_type": "text"
            }
        ]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
    }
}
