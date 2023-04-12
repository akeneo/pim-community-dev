<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidFromIdentifierQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetRawProductsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetRawProductsQueryTest extends IntegrationTestCase
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

        $result = self::getContainer()->get(GetRawProductsQuery::class)->execute($catalog, null, 10);

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
                'identifier' => 'tshirt-blue',
                'is_enabled' => true,
                'product_model_code' => null,
                'created' => new \DateTimeImmutable('2022-08-30T15:30:00+00:00'),
                'updated' => new \DateTimeImmutable('2022-08-30T15:30:00+00:00'),
                'family_code' => null,
                'group_codes' => [],
                'raw_values' => [
                    'sku' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue',
                        ],
                    ],
                    'name' => [
                        'print' => [
                            'en_US' => 'Indigo',
                            'fr_FR' => 'Indigo',
                        ],
                        'ecommerce' => [
                            'en_US' => 'Blue',
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testItReturnsRawProductsUsingUpdatedAfter(): void
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

        $result = self::getContainer()->get(GetRawProductsQuery::class)->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-green'),
                'identifier' => 'tshirt-green',
                'is_enabled' => true,
                'product_model_code' => null,
                'created' => new \DateTimeImmutable('2022-09-01T15:40:00+00:00'),
                'updated' => new \DateTimeImmutable('2022-09-01T15:40:00+00:00'),
                'family_code' => null,
                'group_codes' => [],
                'raw_values' => [
                    'sku' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-green',
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testItReturnsRawProductsUsingUpdatedBefore(): void
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

        $result = self::getContainer()->get(GetRawProductsQuery::class)->execute($catalog, null, 100, null, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-blue'),
                'identifier' => 'tshirt-blue',
                'is_enabled' => true,
                'product_model_code' => null,
                'created' => new \DateTimeImmutable('2022-09-01T15:30:00+00:00'),
                'updated' => new \DateTimeImmutable('2022-09-01T15:30:00+00:00'),
                'family_code' => null,
                'group_codes' => [],
                'raw_values' => [
                    'sku' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue',
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    public function testItReturnsRawProductsUsingUpdatedBeforeAndUpdatedAfter(): void
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

        $result = self::getContainer()->get(GetRawProductsQuery::class)->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00', '2022-09-01T17:45:00+02:00');

        $this->assertEquals([
            [
                'uuid' => $this->findProductUuid('tshirt-green'),
                'identifier' => 'tshirt-green',
                'is_enabled' => true,
                'product_model_code' => null,
                'created' => new \DateTimeImmutable('2022-09-01T15:40:00+00:00'),
                'updated' => new \DateTimeImmutable('2022-09-01T15:40:00+00:00'),
                'family_code' => null,
                'group_codes' => [],
                'raw_values' => [
                    'sku' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-green',
                        ],
                    ],
                ],
            ],
        ], $result);
    }

    private function findProductUuid(string $identifier): UuidInterface
    {
        return Uuid::fromString(self::getContainer()->get(GetProductUuidFromIdentifierQueryInterface::class)->execute($identifier));
    }
}
