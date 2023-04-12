<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidFromIdentifierQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductsWithFilteredValuesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductsWithFilteredValuesQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsProductsMatchingTheCatalog(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(false)]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
            ],
        ], $result);
    }

    public function testItReturnsProductsWithEmptyFilters(): void
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

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            catalogProductValueFilters: [
                'channels' => [],
                'locales' => [],
                'currencies' => [],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Indigo'),
            new SetTextValue('name', 'print', 'fr_FR', 'Indigo'),
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(false)]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
                            'data' => 'Indigo',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope' => 'print',
                            'data' => 'Indigo',
                        ],
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
            ],
        ], $result);
    }

    public function testItReturnsProductsWithValuesFilteredByChannels(): void
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

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            catalogProductValueFilters: [
                'channels' => ['ecommerce'],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Indigo'),
        ]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
            ],
        ], $result);
    }

    public function testItReturnsProductsWithValuesFilteredByLocales(): void
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
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            catalogProductValueFilters: [
                'locales' => ['en_US', 'fr_FR'],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'print', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'fr_FR', 'Bleu'),
            new SetTextValue('name', 'print', 'de_DE', 'Blau'),
        ]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
            ],
        ], $result);
    }

    public function testItReturnsProductsWithValuesFilteredByCurrencies(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US'], ['USD', 'EUR', 'GBP']);
        $this->enableCurrency('GBP');
        $this->createAttribute([
            'code' => 'price',
            'type' => 'pim_catalog_price_collection',
        ]);
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            catalogProductValueFilters: [
                'currencies' => ['USD', 'EUR'],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetPriceCollectionValue('price', null, null, [
                new PriceValue(10, 'USD'),
                new PriceValue(10, 'EUR'),
                new PriceValue(10, 'GBP'),
            ]),
        ]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
            ],
        ], $result);
    }

    public function testItReturnsProductsWithValuesFilteredByAllAvailableFilters(): void
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
        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
            catalogProductValueFilters: [
                'channels' => ['print'],
                'locales' => ['en_US'],
                'currencies' => ['USD'],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
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

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
            ],
        ], $result);
    }

    public function testItReturnsProductsUsingUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-green'),
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
                            'data' => 'tshirt-green',
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
                'created' => '2022-09-01T15:40:00+00:00',
                'updated' => '2022-09-01T15:40:00+00:00',
            ],
        ], $result);
    }

    public function testItReturnsProductsUsingUpdatedBefore(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 100, null, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
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
                'created' => '2022-09-01T15:30:00+00:00',
                'updated' => '2022-09-01T15:30:00+00:00',
            ],
        ], $result);
    }

    public function testItReturnsProductsUsingUpdatedBeforeAndUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'owner',
            catalogProductSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = self::getContainer()->get(GetCatalogQueryInterface::class)->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class)->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00', '2022-09-01T17:45:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-green'),
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
                            'data' => 'tshirt-green',
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
                'created' => '2022-09-01T15:40:00+00:00',
                'updated' => '2022-09-01T15:40:00+00:00',
            ],
        ], $result);
    }

    private function findProductUuid(string $identifier): string
    {
        return self::getContainer()->get(GetProductUuidFromIdentifierQueryInterface::class)->execute($identifier);
    }
}
