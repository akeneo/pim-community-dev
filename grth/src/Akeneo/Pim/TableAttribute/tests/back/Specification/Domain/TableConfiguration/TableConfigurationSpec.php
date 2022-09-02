<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableConfigurationSpec extends ObjectBehavior
{
    function it_must_only_contain_column_definitions()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[new \stdClass()]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_must_have_at_least_two_columns(ColumnDefinition $definition)
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[$definition]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_have_the_same_column_code_twice()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('ingredient'), 'code' => 'INGredient']),
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The column codes are not unique'))->duringInstantiation();
    }

    function it_cannot_have_the_same_column_id_twice()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'quantity']),
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The column ids are not unique'))->duringInstantiation();
    }

    function it_is_initializable(ColumnDefinition $ingredients, ColumnDefinition $quantity)
    {
        $ingredients->id()->willReturn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $quantity->id()->willReturn(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $ingredients->code()->willReturn(ColumnCode::fromString('ingredients'));
        $quantity->code()->willReturn(ColumnCode::fromString('quantity'));
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredients, $quantity]]);
        $this->shouldHaveType(TableConfiguration::class);
    }

    function it_returns_the_first_column_code()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            4 => SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            2 => NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
        ]]);

        $this->getFirstColumnCode()
            ->shouldBeLike(ColumnCode::fromString('ingredient'));
    }

    function it_returns_the_select_columns(){
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'isAllergenic']),
        ]]);

        $this->getSelectColumns()
            ->shouldBeLike([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'isAllergenic']),
            ]);
    }

    function it_returns_validations_given_a_column_code()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'validations' => ['min' => 5, 'max' => 20]]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description', 'validations' => ['max_length' => 50]]),
        ]]);

        $this->getValidations(ColumnCode::fromString('ingredient'))
            ->shouldBeLike(ValidationCollection::createEmpty());
        $this->getValidations(ColumnCode::fromString('quantity'))
            ->shouldBeLike(ValidationCollection::fromNormalized(ColumnDataType::fromString('number'), ['min' => 5, 'max' => 20]));
        $this->getValidations(ColumnCode::fromString('DESCRIPtion'))
            ->shouldBeLike(ValidationCollection::fromNormalized(ColumnDataType::fromString('text'), ['max_length' => 50]));
        $this->getValidations(ColumnCode::fromString('unknown'))
            ->shouldBe(null);
    }

    function it_must_have_a_select_or_reference_entity_column_as_first_column()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'is_required_for_completeness' => true]),
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
        ]]);

        $this->shouldThrow(new \InvalidArgumentException('The first column has an invalid type'))
            ->duringInstantiation();
    }

    function it_can_be_created_with_reference_entity_column_as_first_column()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'record', 'reference_entity_identifier' => 'entity', 'is_required_for_completeness' => true]),
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
        ]]);

        $this->getFirstColumnId()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::record()));
    }

    function it_returns_column_by_id()
    {
        $ingredientColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]);
        $quantityColumn = NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']);
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredientColumn, $quantityColumn]]);

        $this->getColumn(ColumnId::fromString(ColumnIdGenerator::ingredient()))->shouldReturn($ingredientColumn);
        $this->getColumn(ColumnId::fromString(ColumnIdGenerator::quantity()))->shouldReturn($quantityColumn);
        $this->getColumn(ColumnId::fromString(ColumnIdGenerator::generateAsString('unknown')))->shouldReturn(null);
    }

    function it_returns_column_by_string_id()
    {
        $ingredientColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]);
        $quantityColumn = NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']);
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredientColumn, $quantityColumn]]);

        $this->getColumnFromStringId(ColumnIdGenerator::ingredient())->shouldReturn($ingredientColumn);
        $this->getColumnFromStringId(ColumnIdGenerator::quantity())->shouldReturn($quantityColumn);
        $this->getColumnFromStringId(ColumnIdGenerator::generateAsString('unknown'))->shouldReturn(null);
    }

    function it_returns_required_for_completeness_columns()
    {
        $ingredientColumn = SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]);
        $quantityColumn = NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'is_required_for_completeness' => true]);
        $descriptionColumn = TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'code' => 'description', 'is_required_for_completeness' => false]);
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredientColumn, $quantityColumn, $descriptionColumn]]);

        $this->requiredColumns()->shouldReturn([ColumnIdGenerator::ingredient() => $ingredientColumn, ColumnIdGenerator::quantity()=> $quantityColumn]);
    }
}
