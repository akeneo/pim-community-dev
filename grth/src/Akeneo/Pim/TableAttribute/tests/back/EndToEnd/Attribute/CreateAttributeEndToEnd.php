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

final class CreateAttributeEndToEnd extends AbstractAttributeApiTestCase
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
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes/a_table_attribute', [
            'with_table_select_options' => true,
        ]);
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
                'validations' => [],
                'options' => [['code' => 'sugar', 'labels' => ['en_US' => 'Sugar', 'fr_FR' => 'Sucre']]],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => [],
                'validations' => [
                    'max_length' => 100,
                ]
            ]
        ], $decoded['table_configuration']);
    }
}
