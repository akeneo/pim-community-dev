<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\TableColumnTranslator;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\File\TableValuesColumnSorter;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class TableValuesColumnSorterSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        TranslatorInterface $translator,
        TableColumnTranslator $tableColumnTranslator
    ) {
        $translator->trans('pim_table.export_with_label.product', [], null, 'en_US')->willReturn('Product');
        $translator->trans('pim_table.export_with_label.product_model', [], null, 'en_US')->willReturn('Product Model');
        $translator->trans('pim_table.export_with_label.attribute', [], null, 'en_US')->willReturn('Attribute');

        $this->beConstructedWith($tableConfigurationRepository, $translator, $tableColumnTranslator);
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
            ['filters' => ['table_attribute_code' => 'nutrition']]
        )->shouldReturn(['product', 'attribute', 'ingredient', 'quantity', 'extra']);
    }

    function it_sorts_columns_with_labels(
        TableConfigurationRepository $tableConfigurationRepository,
        TableColumnTranslator $tableColumnTranslator
    ) {
        $tableAttribute = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'validations' => ['min' => 5, 'max' => 20]]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description', 'validations' => ['max_length' => 50]]),
        ]);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn($tableAttribute);
        $tableColumnTranslator->getTableColumnLabels('nutrition', 'en_US', ['Product', 'Product Model', 'Attribute'])
            ->willReturn(['Ingredient', 'Quantity', 'Description']);

        $this->sort(
            ['Quantity', 'Ingredient', 'Attribute', 'Product', 'extra'],
            [
                'filters' => ['table_attribute_code' => 'nutrition'],
                'header_with_label' => true,
                'file_locale' => 'en_US',
            ]
        )->shouldReturn(['Product', 'Attribute', 'Ingredient', 'Quantity', 'extra']);
    }

    function it_should_not_sort_when_table_does_not_exist(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willThrow(new TableConfigurationNotFoundException());

        $this->sort(
            ['quantity', 'ingredient', 'attribute', 'product', 'extra'],
            ['filters' => ['table_attribute_code' => 'nutrition']]
        )->shouldReturn(['product', 'attribute', 'quantity', 'ingredient', 'extra']);
    }
}
