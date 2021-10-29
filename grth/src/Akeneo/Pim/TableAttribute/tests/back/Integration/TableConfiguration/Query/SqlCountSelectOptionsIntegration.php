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

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query\SqlCountSelectOptions;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class SqlCountSelectOptionsIntegration extends TestCase
{
    private SqlCountSelectOptions $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->get(CountSelectOptions::class);
    }

    /** @test */
    public function it_counts_the_select_options(): void
    {
        self::assertSame(0, $this->query->all());

        $this->createTableAttribute('packaging', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'dimension', 'options' => [
                ['code' => 'width', 'labels' => ['en_US' => 'Width']],
                ['code' => 'height', 'labels' => ['en_US' => 'Height']],
                ['code' => 'depth'],
            ]],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'measure'],
        ]);
        $this->createTableAttribute('nutrition_us', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'ingredient', 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                ['code' => 'egg'],
            ]],
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'score', 'options' => [
                ['code' => 'D', 'labels' => ['en_US' => 'D']],
                ['code' => 'B', 'labels' => ['en_US' => 'B']],
                ['code' => 'C', 'labels' => ['en_US' => 'C']],
                ['code' => 'E', 'labels' => ['en_US' => 'E']],
                ['code' => 'A', 'labels' => ['en_US' => 'A']],
            ]],
        ]);

        self::assertSame(11, $this->query->all());
    }

    /** @test */
    public function it_counts_the_select_options_by_attribute_and_column(): void
    {
        $this->createTableAttribute('packaging', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'dimension', 'options' => [
                ['code' => 'width', 'labels' => ['en_US' => 'Width']],
                ['code' => 'height', 'labels' => ['en_US' => 'Height']],
                ['code' => 'depth'],
            ]],
            ['data_type' => NumberColumn::DATATYPE, 'code' => 'measure'],
        ]);
        $this->createTableAttribute('nutrition_us', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'ingredient', 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                ['code' => 'egg'],
            ]],
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'score', 'options' => [
                ['code' => 'D', 'labels' => ['en_US' => 'D']],
                ['code' => 'B', 'labels' => ['en_US' => 'B']],
                ['code' => 'C', 'labels' => ['en_US' => 'C']],
                ['code' => 'E', 'labels' => ['en_US' => 'E']],
                ['code' => 'A', 'labels' => ['en_US' => 'A']],
            ]],
        ]);
        $this->createTableAttribute('nutrition_eu', [
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'ingredient', 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']],
                ['code' => 'egg'],
            ]],
            ['data_type' => SelectColumn::DATATYPE, 'code' => 'score', 'options' => [
                ['code' => 'D', 'labels' => ['en_US' => 'D']],
                ['code' => 'B', 'labels' => ['en_US' => 'B']],
                ['code' => 'C', 'labels' => ['en_US' => 'C']],
                ['code' => 'A', 'labels' => ['en_US' => 'A']],
            ]],
        ]);

        self::assertSame(3, $this->query->forAttributeAndColumn('packaging', ColumnCode::fromString('dimension')));
        self::assertSame(0, $this->query->forAttributeAndColumn('packaging', ColumnCode::fromString('unknown')));

        self::assertSame(3, $this->query->forAttributeAndColumn('nutrition_us', ColumnCode::fromString('ingredient')));
        self::assertSame(5, $this->query->forAttributeAndColumn('nutrition_us', ColumnCode::fromString('score')));

        self::assertSame(3, $this->query->forAttributeAndColumn('nutrition_eu', ColumnCode::fromString('ingredient')));
        self::assertSame(4, $this->query->forAttributeAndColumn('nutrition_eu', ColumnCode::fromString('score')));

        self::assertSame(0, $this->query->forAttributeAndColumn('unknown', ColumnCode::fromString('ingredient')));
        self::assertSame(0, $this->query->forAttributeAndColumn('unknown', ColumnCode::fromString('unknown')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createTableAttribute(string $attributeCode, array $tableConfig): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => $attributeCode,
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'localizable' => false,
            'scopable' => false,
            'table_configuration' => $tableConfig,
        ]);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string)$violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
