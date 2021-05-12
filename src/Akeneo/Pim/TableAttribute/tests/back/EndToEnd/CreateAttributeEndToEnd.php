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
                "type": "text"
            }
        ]
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
        $response = $client->getResponse();

        print_r($response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testItCreatesAValidTableAttribute(): void
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
                "type": "text"
            },
            {
                "code": "quantity",
                "type": "text"
            }
        ]
    }
JSON;

        $client = $this->createAuthenticatedClient();
        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);
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
                'data_type' => 'text',
                'labels' => [],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => [],
            ]
        ], $decoded['table_configuration']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
