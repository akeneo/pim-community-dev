<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Enterprise\EndToEnd\Attribute;

use Akeneo\Test\Pim\TableAttribute\EndToEnd\Attribute\AbstractAttributeApiTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateAttributeEndToEnd extends AbstractAttributeApiTestCase
{
    public function testItCreatesAValidTableAttributeWithARecordColumn(): void
    {
        $client = $this->createAuthenticatedClient();
        $data =
            <<<JSON
    {
        "code":"a_table_attribute_with_record",
        "type":"pim_catalog_table",
        "group":"attributeGroupA",
        "table_configuration": [
            {
                "code": "ingredients",
                "data_type": "select"
            },
            {
                "code": "record",
                "data_type": "record",
                "reference_entity_code": "entity"
            }
        ]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes/a_table_attribute_with_record', [
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
                'labels' => [],
                'validations' => [],
                'is_required_for_completeness' => true,
                'options' => [],
            ],
            [
                'code' => 'record',
                'data_type' => 'record',
                'labels' => [],
                'validations' => [],
                'is_required_for_completeness' => false,
                'reference_entity_code' => 'entity',
            ]
        ], $decoded['table_configuration']);
    }
}