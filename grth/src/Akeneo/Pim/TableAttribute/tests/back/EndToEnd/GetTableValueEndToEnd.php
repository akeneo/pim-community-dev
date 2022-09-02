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

namespace Akeneo\Test\Pim\TableAttribute\EndToEnd;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetTableValueEndToEnd extends ApiTestCase
{
    public function testItGetTableProductValue(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/products/id1');
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $expectedValues = [
            'nutrition' => [
                [
                    'locale' => null,
                    'scope' => null,
                    'data' => [
                        [
                            'ingredient' => 'sugar',
                            'quantity' => 5,
                            'manufacturing_time' => ['amount' => 2, 'unit' => 'MINUTE'],
                        ],
                    ]
                ]
            ]
        ];

        Assert::assertEqualsCanonicalizing(
            $expectedValues,
            \json_decode($response->getContent(), true)['values'] ?? null
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
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
                ['code' => 'ingredient', 'data_type' => 'select', 'labels' => ['en_US' => 'ingredient'],
                    'options' => [
                        ['code' => 'sugar']
                    ]],
                ['code' => 'quantity', 'data_type' => 'number', 'labels' => ['en_US' => 'Quantity']],
                [
                    'code' => 'manufacturing_time',
                    'data_type' => 'measurement',
                    'measurement_family_code' => 'Duration',
                    'measurement_default_unit_code' => 'Day',
                ],
            ]
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $product = $this->get('pim_catalog.builder.product')->createProduct('id1');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'nutrition' => [
                ['locale' => null, 'scope' => null, 'data' => [
                    ['ingredient' => 'sugar', 'quantity' => 5, 'manufacturing_time' => ['amount' => 2, 'unit' => 'MINUTE']],
                ]],
            ],
        ]]);
        self::assertInstanceOf(TableValue::class, $product->getValue('nutrition'));

        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        self::assertCount(0, $violations, sprintf('Product is not valid: %s', $violations));

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
