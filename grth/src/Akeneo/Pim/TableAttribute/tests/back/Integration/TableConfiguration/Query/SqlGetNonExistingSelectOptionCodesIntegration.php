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

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetNonExistingSelectOptionCodes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query\SqlGetNonExistingSelectOptionCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class SqlGetNonExistingSelectOptionCodesIntegration extends TestCase
{
    private Connection $connection;
    private SqlGetNonExistingSelectOptionCodes $sqlGetNonExistingSelectOptionCodes;

    private int $nutritionAttributeId;
    private int $nutritionCopyAttributeId;
    private string $nutritionIngredientColumnId;
    private string $nutritionCopyIngredientColumnId;

    /** @test */
    public function it_returns_non_existing_option_codes(): void
    {
        $nonExistingCodes = $this->sqlGetNonExistingSelectOptionCodes->forOptionCodes(
            'nutrition',
            ColumnCode::fromString('ingredients'),
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')]
        );
        self::assertEqualsCanonicalizing([SelectOptionCode::fromString('onion')], $nonExistingCodes);

        $nonExistingCodes = $this->sqlGetNonExistingSelectOptionCodes->forOptionCodes(
            'NUTrition',
            ColumnCode::fromString('INGredients'),
            [SelectOptionCode::fromString('SALT'), SelectOptionCode::fromString('pepPER'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')]
        );
        self::assertEqualsCanonicalizing([SelectOptionCode::fromString('onion')], $nonExistingCodes);

        $nonExistingCodes = $this->sqlGetNonExistingSelectOptionCodes->forOptionCodes(
            'nutrition_copy',
            ColumnCode::fromString('ingredients'),
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')]
        );
        self::assertEqualsCanonicalizing(
            [SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')],
            $nonExistingCodes
        );

        $nonExistingCodes = $this->sqlGetNonExistingSelectOptionCodes->forOptionCodes(
            'nutrition',
            ColumnCode::fromString('quantity'),
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')]
        );
        self::assertEqualsCanonicalizing(
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')],
            $nonExistingCodes
        );

        $nonExistingCodes = $this->sqlGetNonExistingSelectOptionCodes->forOptionCodes(
            'unknown',
            ColumnCode::fromString('quantity'),
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')]
        );
        self::assertEqualsCanonicalizing(
            [SelectOptionCode::fromString('salt'), SelectOptionCode::fromString('pepper'), SelectOptionCode::fromString('onion'), SelectOptionCode::fromString('garlic')],
            $nonExistingCodes
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->sqlGetNonExistingSelectOptionCodes = $this->get(GetNonExistingSelectOptionCodes::class);

        $this->nutritionAttributeId = $this->createAttribute('nutrition');
        $this->nutritionCopyAttributeId = $this->createAttribute('nutrition_copy');

        $this->nutritionIngredientColumnId = $this->createColumn(
            $this->nutritionAttributeId,
            'ingredients',
            'select',
            0
        );
        $this->createColumn(
            $this->nutritionAttributeId,
            'quantity',
            'select',
            1
        );
        $this->nutritionCopyIngredientColumnId = $this->createColumn(
            $this->nutritionCopyAttributeId,
            'ingredients',
            'select',
            0
        );

        $this->createOption($this->nutritionIngredientColumnId, 'salt', ['en_US' => 'Salt', 'fr_FR' => 'Sel']);
        $this->createOption($this->nutritionIngredientColumnId, 'pepper', ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']);
        $this->createOption($this->nutritionIngredientColumnId, 'garlic', ['en_US' => 'Garlic', 'fr_FR' => 'Ail']);
        $this->createOption($this->nutritionCopyIngredientColumnId, 'salt', ['en_US' => 'Salt', 'fr_FR' => 'Sel']);
        $this->createOption($this->nutritionCopyIngredientColumnId, 'pepper', ['en_US' => 'Pepper', 'fr_FR' => 'Poivre']);
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
        INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, is_required_for_completeness)
        VALUES (:id, :attribute_id, :code, :data_type, :column_order, :is_required_for_completeness)
        SQL;

        $this->connection->executeQuery($insertColumnQuery, [
            'id' => $attributeId . $code,
            'attribute_id' => $attributeId,
            'code' => $code,
            'data_type' => $dataType,
            'column_order' => $order,
            'is_required_for_completeness' => '1',
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
