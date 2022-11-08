<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidFromIdentifierQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductsWithFilteredValuesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductsQueryTest extends IntegrationTestCase
{
    private ?GetCatalogQueryInterface $getCatalogQuery;
    private ?GetProductsWithFilteredValuesQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));

        $this->purgeDataAndLoadMinimalCatalog();

        $this->getCatalogQuery = self::getContainer()->get(GetCatalogQueryInterface::class);
        $this->query = self::getContainer()->get(GetProductsWithFilteredValuesQuery::class);
    }

    public function testItReturnsProductsMatchingTheCatalog(): void
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
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [
            new SetEnabled(true),
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Indigo'),
            new SetTextValue('name', 'print', 'fr_FR', 'Indigo'),
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-08-30T15:30:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(false)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 10);

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

    public function testItReturnsProductsUsingUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00');

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

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, null, '2022-09-01T17:35:00+02:00');

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

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00', '2022-09-01T17:45:00+02:00');

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
