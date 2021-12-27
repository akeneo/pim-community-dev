<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableColumnTranslator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableColumnTranslatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->beConstructedWith($tableConfigurationRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableColumnTranslator::class);
    }

    function it_translates_the_table_columns(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => [
                'en_US' => 'Ingredient US',
            ], 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [
                'fr_FR' => 'Quantité',
                'en_US' => 'Quantity',
            ]]),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'allergenic']),
        ]));

        $this->getTableColumnLabels('nutrition', 'en_US')->shouldReturn([
            'ingredient' => 'Ingredient US',
            'quantity' => 'Quantity',
            'allergenic' => '[allergenic]',
        ]);
        $this->getTableColumnLabels('nutrition', 'fr_FR')->shouldReturn([
            'ingredient' => '[ingredient]',
            'quantity' => 'Quantité',
            'allergenic' => '[allergenic]',
        ]);
    }

    function it_translates_the_table_columns_with_duplicate(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => [
                'en_US' => 'Ingredient US',
            ], 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [
                'fr_FR' => 'Quantité',
                'en_US' => 'Quantity',
            ]]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('other_quantity'), 'code' => 'other_quantity', 'labels' => [
                'fr_FR' => 'Quantité',
                'en_US' => 'Other quantity',
            ]]),
        ]));

        $this->getTableColumnLabels('nutrition', 'en_US')->shouldReturn([
            'ingredient' => 'Ingredient US',
            'quantity' => 'Quantity',
            'other_quantity' => 'Other quantity',
        ]);
        $this->getTableColumnLabels('nutrition', 'fr_FR')->shouldReturn([
            'ingredient' => '[ingredient]',
            'quantity' => 'Quantité--quantity',
            'other_quantity' => 'Quantité--other_quantity',
        ]);
    }

    function it_translates_the_table_columns_with_forbidden_labels(
        TableConfigurationRepository $tableConfigurationRepository
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'labels' => [
                'en_US' => 'Ingredient US',
            ], 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'labels' => [
                'en_US' => 'Quantity',
            ]]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('other_quantity'), 'code' => 'other_quantity', 'labels' => [
                'en_US' => 'Other quantity',
            ]]),
        ]));

        $this->getTableColumnLabels('nutrition', 'en_US', ['Quantity', 'Ingredient US'])->shouldReturn([
            'ingredient' => 'Ingredient US--ingredient',
            'quantity' => 'Quantity--quantity',
            'other_quantity' => 'Other quantity',
        ]);
    }
}
