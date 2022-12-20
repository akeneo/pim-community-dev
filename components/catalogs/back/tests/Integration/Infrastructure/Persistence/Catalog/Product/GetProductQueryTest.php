<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductQueryTest extends IntegrationTestCase
{
    private ?GetProductQuery $getProductQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));

        $this->purgeDataAndLoadMinimalCatalog();

        $this->getProductQuery = self::getContainer()->get(GetProductQuery::class);
    }

    public function testItReturnsAProductMatchingTheCatalog(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $catalog = new Catalog(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'owner',
            true,
            [],
            [],
            [],
        );

        $productEnabled = $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);
        $this->createProduct('tshirt-green', [new SetEnabled(false)]);
        $productEnabledUuid = (string) $productEnabled->getUuid();

        $result = $this->getProductQuery->execute($catalog, $productEnabledUuid);

        $this->assertEquals([
            'uuid' => $productEnabledUuid,
            'enabled' => true,
            'family' => null,
            'categories' => [],
            'groups' => [],
            'parent' => null,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt-blue',
                    ],
                ],
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => (object) [],
            'created' => '2022-08-30T15:30:00+00:00',
            'updated' => '2022-08-30T15:30:00+00:00',
        ], $result);
    }

    public function testItReturnsAProductWithValuesFilteredByChannels(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $catalog = new Catalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            enabled: true,
            productSelectionCriteria: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            productValueFilters: ['channels' => ['ecommerce']],
            productMapping: [],
        );

        $product = $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Indigo'),
        ]);
        $productUuid = (string) $product->getUuid();

        $result = $this->getProductQuery->execute($catalog, $productUuid);

        $this->assertEquals([
            'uuid' => $productUuid,
            'enabled' => true,
            'family' => null,
            'categories' => [],
            'groups' => [],
            'parent' => null,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt-blue',
                    ],
                ],
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'Blue',
                    ],
                ],
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => (object) [],
            'created' => '2022-08-30T15:30:00+00:00',
            'updated' => '2022-08-30T15:30:00+00:00',
        ], $result);
    }

    public function testItReturnsAProductWithValuesFilteredByLocales(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US', 'fr_FR', 'de_DE']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);

        $catalog = new Catalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            enabled: true,
            productSelectionCriteria: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            productValueFilters: ['locales' => ['en_US', 'fr_FR']],
            productMapping: [],
        );


        $product = $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'print', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'fr_FR', 'Bleu'),
            new SetTextValue('name', 'print', 'de_DE', 'Blau'),
        ]);
        $productUuid = (string) $product->getUuid();

        $result = $this->getProductQuery->execute($catalog, $productUuid);

        $this->assertEquals([
            'uuid' => $productUuid,
            'enabled' => true,
            'family' => null,
            'categories' => [],
            'groups' => [],
            'parent' => null,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt-blue',
                    ],
                ],
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'print',
                        'data' => 'Blue',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope' => 'print',
                        'data' => 'Bleu',
                    ],
                ],
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => (object) [],
            'created' => '2022-08-30T15:30:00+00:00',
            'updated' => '2022-08-30T15:30:00+00:00',
        ], $result);
    }

    public function testItReturnsAProductWithValuesFilteredByCurrencies(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US'], ['USD', 'EUR', 'GBP']);
        $this->enableCurrency('GBP');
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
        ]);

        $catalog = new Catalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            enabled: true,
            productSelectionCriteria: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            productValueFilters: ['currencies' => ['USD', 'EUR']],
            productMapping: [],
        );

        $product = $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetPriceCollectionValue('price', null, null, [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
                new PriceValue(10, 'GBP'),
            ]),
        ]);

        $productUuid = (string) $product->getUuid();
        $result = $this->getProductQuery->execute($catalog, $productUuid);

        $this->assertEquals([
            'uuid' => $productUuid,
            'enabled' => true,
            'family' => null,
            'categories' => [],
            'groups' => [],
            'parent' => null,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt-blue',
                    ],
                ],
                'price' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [
                            [
                                'amount' => 10,
                                'currency' => 'EUR',
                            ],
                            [
                                'amount' => 10,
                                'currency' => 'USD',
                            ],
                        ],
                    ],
                ],
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => (object) [],
            'created' => '2022-08-30T15:30:00+00:00',
            'updated' => '2022-08-30T15:30:00+00:00',
        ], $result);
    }

    public function testItReturnsAProductWithValuesFilteredByAllAvailableFilters(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US', 'fr_FR'], ['USD', 'EUR']);
        $this->createChannel('mobile', ['en_US', 'fr_FR'], ['USD', 'EUR']);
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
            'scopable' => true,
            'localizable' => true,
        ]);

        $catalog = new Catalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            enabled: true,
            productSelectionCriteria: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            productValueFilters: [
                'channels' => ['print'],
                'locales' => ['en_US'],
                'currencies' => ['USD'],
            ],
            productMapping: [],
        );

        $product = $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetPriceCollectionValue('price', 'print', 'en_US', [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
            ]),
            new SetPriceCollectionValue('price', 'print', 'fr_FR', [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
            ]),
            new SetPriceCollectionValue('price', 'mobile', 'en_US', [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
            ]),
            new SetPriceCollectionValue('price', 'mobile', 'fr_FR', [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
            ]),
        ]);
        $productUuid = (string) $product->getUuid();

        $result = $this->getProductQuery->execute($catalog, $productUuid);

        $this->assertEquals([
            'uuid' => $productUuid,
            'enabled' => true,
            'family' => null,
            'categories' => [],
            'groups' => [],
            'parent' => null,
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'tshirt-blue',
                    ],
                ],
                'price' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'print',
                        'data' => [
                            [
                                'amount' => 10,
                                'currency' => 'USD',
                            ],
                        ],
                    ],
                ],
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'UPSELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'X_SELL' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'products' => [],
                    'product_models' => [],
                ],
            ],
            'quantified_associations' => (object) [],
            'created' => '2022-08-30T15:30:00+00:00',
            'updated' => '2022-08-30T15:30:00+00:00',
        ], $result);
    }
}
