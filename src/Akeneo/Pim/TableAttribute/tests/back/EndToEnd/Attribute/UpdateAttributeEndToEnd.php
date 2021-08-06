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

namespace Akeneo\Pim\TableAttribute\tests\back\EndToEnd\Attribute;

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
}
