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

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeListEndToEnd extends AbstractAttributeApiTestCase
{
    public function testItListsTableAttributes(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes', ['limit' => 100]);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $decoded = \json_decode($response->getContent(), true);

        $nutritionTableConfiguration = $this->getAttributeInResult($decoded, 'nutrition');
        self::assertNotNull($nutritionTableConfiguration);
        self::assertArrayHasKey('table_configuration', $nutritionTableConfiguration);
        self::assertEqualsCanonicalizing([
            [
                'code' => 'ingredients',
                'data_type' => 'select',
                'labels' => ['en_US' => 'Ingredients'],
                'validations' => [],
                'is_required_for_completeness' => true,
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => ['en_US' => 'Quantity'],
                'validations' => ['max_length' => 100],
                'is_required_for_completeness' => false,
            ],
        ], $nutritionTableConfiguration['table_configuration']);

        $dimensionTableConfiguration = $this->getAttributeInResult($decoded, 'dimension');
        self::assertNotNull($dimensionTableConfiguration);
        self::assertArrayHasKey('table_configuration', $dimensionTableConfiguration);
        self::assertEqualsCanonicalizing([
            [
                'code' => 'unity',
                'data_type' => 'select',
                'labels' => ['en_US' => 'Unity'],
                'validations' => [],
                'is_required_for_completeness' => true,
            ],
            [
                'code' => 'value',
                'data_type' => 'number',
                'labels' => ['en_US' => 'Value'],
                'validations' => [],
                'is_required_for_completeness' => false,
            ],
        ], $dimensionTableConfiguration['table_configuration']);
    }

    public function testItListsATableAttributesWithTableSelectOptions(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request(Request::METHOD_GET, 'api/rest/v1/attributes', [
            'limit' => 100,
            'with_table_select_options' => true,
        ]);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $decoded = \json_decode($response->getContent(), true);

        $nutritionTableConfiguration = $this->getAttributeInResult($decoded, 'nutrition');
        self::assertNotNull($nutritionTableConfiguration);
        self::assertArrayHasKey('table_configuration', $nutritionTableConfiguration);
        self::assertEqualsCanonicalizing([
            [
                'code' => 'ingredients',
                'data_type' => 'select',
                'labels' => ['en_US' => 'Ingredients'],
                'validations' => [],
                'is_required_for_completeness' => true,
                'options' => [
                    ['code' => 'salt', 'labels' => []],
                    ['code' => 'sugar', 'labels' => []],
                ],
            ],
            [
                'code' => 'quantity',
                'data_type' => 'text',
                'labels' => ['en_US' => 'Quantity'],
                'validations' => ['max_length' => 100],
                'is_required_for_completeness' => false,
            ],
        ], $nutritionTableConfiguration['table_configuration']);

        $dimensionTableConfiguration = $this->getAttributeInResult($decoded, 'dimension');
        self::assertNotNull($dimensionTableConfiguration);
        self::assertArrayHasKey('table_configuration', $dimensionTableConfiguration);
        self::assertEqualsCanonicalizing([
            [
                'code' => 'unity',
                'data_type' => 'select',
                'labels' => ['en_US' => 'Unity'],
                'validations' => [],
                'is_required_for_completeness' => true,
                'options' => [
                    ['code' => 'width', 'labels' => []],
                    ['code' => 'height', 'labels' => []],
                ]
            ],
            [
                'code' => 'value',
                'data_type' => 'number',
                'labels' => ['en_US' => 'Value'],
                'validations' => [],
                'is_required_for_completeness' => false,
            ],
        ], $dimensionTableConfiguration['table_configuration']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attribute = new Attribute();
        $attribute->setEntityType(Product::class);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'],
                    'options' => [
                        ['code' => 'salt'],
                        ['code' => 'sugar'],
                    ],
                    'validations' => [],
                ],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => ['en_US' => 'Quantity'], 'validations' => ['max_length' => 100]],
            ]
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $attribute = new Attribute();
        $attribute->setEntityType(Product::class);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'dimension',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                ['code' => 'unity', 'data_type' => 'select', 'labels' => ['en_US' => 'Unity'],
                    'options' => [
                        ['code' => 'width'],
                        ['code' => 'height'],
                    ],
                ],
                ['code' => 'value', 'data_type' => 'number', 'labels' => ['en_US' => 'Value']],
            ]
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function getAttributeInResult(array $results, string $attributeCode): ?array
    {
        foreach ($results['_embedded']['items'] as $result) {
            if ($result['code'] === $attributeCode) {
                return $result;
            }
        }

        return null;
    }
}
