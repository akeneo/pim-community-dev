<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File\TableValuesColumnSorter;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use PhpSpec\ObjectBehavior;

class TableValuesColumnSorterSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->beConstructedWith($tableConfigurationRepository);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(TableValuesColumnSorter::class);
        $this->shouldImplement(ColumnSorterInterface::class);
    }

    function it_sorts_columns(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableAttribute = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'validations' => ['min' => 5, 'max' => 20]]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description', 'validations' => ['max_length' => 50]]),
        ]);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn($tableAttribute);

        $this->sort(
            ['quantity', 'ingredient', 'attribute', 'product', 'extra'],
            ['filters' => [
                'table_attribute_code' => 'nutrition']
            ]
        )->shouldReturn(['product', 'attribute', 'ingredient', 'quantity', 'extra']);
    }

    function it_should_not_sort_when_table_does_not_exist(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willThrow(new TableConfigurationNotFoundException());

        $this->sort(
            ['quantity', 'ingredient', 'attribute', 'product', 'extra'],
            ['filters' => [
                'table_attribute_code' => 'nutrition']
            ]
        )->shouldReturn(['product', 'attribute', 'quantity', 'ingredient', 'extra']);
    }
}
