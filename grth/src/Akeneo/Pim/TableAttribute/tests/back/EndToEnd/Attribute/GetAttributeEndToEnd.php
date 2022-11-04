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

final class GetAttributeEndToEnd extends AbstractAttributeApiTestCase
{
    public function testItGetsATableAttribute(): void
    {
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
                'validations' => [],
                'is_required_for_completeness' => true,
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => [],
                'validations' => [
                    'max_length' => 100,
                ],
                'is_required_for_completeness' => false,
            ],
            [
                'code' => 'manufacturing_time',
                'data_type' => 'measurement',
                'labels' => [],
                'validations' => [],
                'is_required_for_completeness' => false,
                'measurement_family_code' => 'duration',
                'measurement_default_unit_code' => 'second',
            ],
        ], $decoded['table_configuration']);
    }

    public function testItGetsATableAttributeWithTableSelectOptions(): void
    {
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
                'is_required_for_completeness' => true,
                'options' => [['code' => 'sugar', 'labels' => ['en_US' => 'Sugar', 'fr_FR' => 'Sucre']]],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => [],
                'validations' => [
                    'max_length' => 100,
                ],
                'is_required_for_completeness' => false,
            ],
            [
                'code' => 'manufacturing_time',
                'data_type' => 'measurement',
                'labels' => [],
                'validations' => [],
                'is_required_for_completeness' => false,
                'measurement_family_code' => 'duration',
                'measurement_default_unit_code' => 'second',
            ],
        ], $decoded['table_configuration']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $client = $this->createAuthenticatedClient();
        $this->createValidTableAttribute($client);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
