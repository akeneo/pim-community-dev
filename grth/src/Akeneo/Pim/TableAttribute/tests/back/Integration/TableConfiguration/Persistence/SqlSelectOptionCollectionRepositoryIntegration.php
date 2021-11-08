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

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Persistence;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class SqlSelectOptionCollectionRepositoryIntegration extends TestCase
{
    private Connection $connection;
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;

    /** @test */
    public function it_returns_option_collection(): void
    {
        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']],
                ['code' => 'garlic', 'labels' => ['en_US' => 'Garlic', 'fr_FR' => 'Ail']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->normalize()
        );

        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']],
                ['code' => 'garlic', 'labels' => ['en_US' => 'Garlic', 'fr_FR' => 'Ail']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('NUTrition', ColumnCode::fromString('INGREDIENTS'))->normalize()
        );

        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition_copy', ColumnCode::fromString('ingredients'))->normalize()
        );

        self::assertEqualsCanonicalizing(
            [],
            $this->selectOptionCollectionRepository->getByColumn('nutrition_copy', ColumnCode::fromString('unknown'))->normalize()
        );

        self::assertEqualsCanonicalizing(
            [],
            $this->selectOptionCollectionRepository->getByColumn('unknown', ColumnCode::fromString('ingredients'))->normalize()
        );
    }

    /** @test */
    public function it_saves_option_collection(): void
    {
        $this->selectOptionCollectionRepository->save('nutrition', ColumnCode::fromString('ingredients'), WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized([
            ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ['code' => 'chili', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
            ['code' => 'id', 'labels' => ['en_US' => 'Id', 'fr_FR' => 'Id']],
        ])));
        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ['code' => 'chili', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
                ['code' => 'id', 'labels' => ['en_US' => 'Id', 'fr_FR' => 'Id']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->normalize()
        );
        $this->assertColumnOptionsOfNutritionCopyAreNotUpdated();
    }

    /** @test */
    public function it_saves_option_collection_with_case_insensitive(): void
    {
        $this->selectOptionCollectionRepository->save('NUTrition', ColumnCode::fromString('ingREDIENTS'), WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::fromNormalized([
            ['code' => 'SALT', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ['code' => 'PEpper', 'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre', 'de_DE' => 'Pfeffer']],
            ['code' => 'CHILI', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
        ])));
        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre', 'de_DE' => 'Pfeffer']],
                ['code' => 'CHILI', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->normalize()
        );
        $this->assertColumnOptionsOfNutritionCopyAreNotUpdated();
    }

    /** @test */
    public function it_removes_the_select_options(): void
    {
        $this->selectOptionCollectionRepository->save(
            'nutrition',
            ColumnCode::fromString('ingredients'),
            WriteSelectOptionCollection::fromReadSelectOptionCollection(SelectOptionCollection::empty())
        );
        self::assertEqualsCanonicalizing(
            [],
            $this->selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))->normalize()
        );
        $this->assertColumnOptionsOfNutritionCopyAreNotUpdated();
    }

    /** @test */
    public function it_upserts_select_options()
    {
        $this->selectOptionCollectionRepository->upsert('nutrition', ColumnCode::fromString('ingredients'), SelectOptionCollection::fromNormalized([
            ['code' => 'salt', 'labels' => []],
            ['code' => 'chili', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
            ['code' => 'id', 'labels' => ['en_US' => 'Id', 'fr_FR' => 'Id']],
        ]));
        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => (object)[]],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']],
                ['code' => 'garlic', 'labels' => ['en_US' => 'Garlic', 'fr_FR' => 'Ail']],
                ['code' => 'chili', 'labels' => ['en_US' => 'Chili', 'fr_FR' => 'Piment']],
                ['code' => 'id', 'labels' => ['en_US' => 'Id', 'fr_FR' => 'Id']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))
                                                      ->normalize()
        );
    }

    private function assertColumnOptionsOfNutritionCopyAreNotUpdated(): void
    {
        self::assertEqualsCanonicalizing(
            [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'pepper', 'labels' => ['en_US' => 'Pepper']],
            ],
            $this->selectOptionCollectionRepository->getByColumn('nutrition_copy', ColumnCode::fromString('ingredients'))->normalize()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->selectOptionCollectionRepository = $this->get(SelectOptionCollectionRepository::class);

        $nutritionAttributeId = $this->createAttribute('nutrition');
        $nutritionCopyAttributeId = $this->createAttribute('nutrition_copy');

        $nutritionIngredientColumnId = $this->createColumn(
            $nutritionAttributeId,
            'ingredients',
            'select',
            0
        );
        $this->createColumn(
            $nutritionAttributeId,
            'quantity',
            'select',
            1
        );
        $nutritionCopyIngredientColumnId = $this->createColumn(
            $nutritionCopyAttributeId,
            'ingredients',
            'select',
            0
        );

        $this->createOption($nutritionIngredientColumnId, 'salt', ['en_US' => 'Salt', 'fr_FR' => 'Sel']);
        $this->createOption($nutritionIngredientColumnId, 'pepper', ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']);
        $this->createOption($nutritionIngredientColumnId, 'garlic', ['en_US' => 'Garlic', 'fr_FR' => 'Ail']);
        $this->createOption($nutritionCopyIngredientColumnId, 'salt', ['en_US' => 'Salt']);
        $this->createOption($nutritionCopyIngredientColumnId, 'pepper', ['en_US' => 'Pepper']);
    }

    private function createAttribute(string $code): int
    {
        $insertAttributeQuery = <<<SQL
        INSERT INTO pim_catalog_attribute (sort_order, useable_as_grid_filter, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated, guidelines)
        VALUES (1, 0, 1, 1, 0, 0, :code, 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_table', 'table', '2021-05-18 08:43:55', '2021-05-18 08:43:55', '[]');
        SQL;
        $this->connection->executeQuery($insertAttributeQuery, ['code' => $code]);

        return (int) $this->connection->lastInsertId();
    }

    private function createColumn(int $attributeId, string $code, string $dataType, int $order): string
    {
        $insertColumnQuery = <<<SQL
        INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order)
        VALUES (:id, :attribute_id, :code, :data_type, :column_order)
        SQL;

        $this->connection->executeQuery($insertColumnQuery, [
            'id' => $attributeId . $code,
            'attribute_id' => $attributeId,
            'code' => $code,
            'data_type' => $dataType,
            'column_order' => $order,
        ]);

        return $attributeId . $code;
    }

    private function createOption(string $attributeId, string $code, array $labels = []): void
    {
        $insertOptionQuery = <<<SQL
        INSERT INTO pim_catalog_table_column_select_option (column_id, code, labels)
        VALUES (:column_id, :code, :labels)
        SQL;
        $this->connection->executeQuery($insertOptionQuery, [
            'column_id' => $attributeId,
            'code' => $code,
            'labels' => \json_encode($labels),
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
