<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
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

    function it_cannot_have_the_same_column_twice()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['code' => 'ingredient']),
            TextColumn::fromNormalized(['code' => 'INGredient']),
        ]]);
        $this->shouldThrow(new \InvalidArgumentException('The column codes are not unique'))->duringInstantiation();
    }

    function it_is_initializable(ColumnDefinition $ingredients, ColumnDefinition $quantity)
    {
        $ingredients->code()->willReturn(ColumnCode::fromString('ingredients'));
        $quantity->code()->willReturn(ColumnCode::fromString('quantity'));
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredients, $quantity]]);
        $this->shouldHaveType(TableConfiguration::class);
    }

    function it_returns_the_data_type_of_a_given_column_code()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['code' => 'ingredient']),
            TextColumn::fromNormalized(['code' => 'descripTION']),
            NumberColumn::fromNormalized(['code' => 'quantity']),
        ]]);

        $this->getColumnDataType(ColumnCode::fromString('ingredient'))->shouldBeLike(ColumnDataType::fromString('select'));
        $this->getColumnDataType(ColumnCode::fromString('description'))->shouldBeLike(ColumnDataType::fromString('text'));
        $this->getColumnDataType(ColumnCode::fromString('quantity'))->shouldBeLike(ColumnDataType::fromString('number'));
        $this->getColumnDataType(ColumnCode::fromString('unknown'))->shouldReturn(null);
    }

    function it_returns_the_first_column_code()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            4 => SelectColumn::fromNormalized(['code' => 'ingredient']),
            2 => NumberColumn::fromNormalized(['code' => 'quantity']),
        ]]);

        $this->getFirstColumnCode()
            ->shouldBeLike(ColumnCode::fromString('ingredient'));
    }

    function it_returns_the_select_columns(){
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['code' => 'ingredient']),
            NumberColumn::fromNormalized(['code' => 'quantity']),
            SelectColumn::fromNormalized(['code' => 'isAllergenic']),
        ]]);

        $this->getSelectColumns()
            ->shouldBeLike([
                SelectColumn::fromNormalized(['code' => 'ingredient']),
                SelectColumn::fromNormalized(['code' => 'isAllergenic']),
            ]);
    }

    function it_returns_validations_given_a_column_code()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            SelectColumn::fromNormalized(['code' => 'ingredient']),
            NumberColumn::fromNormalized(['code' => 'quantity', 'validations' => ['min' => 5, 'max' => 20]]),
            TextColumn::fromNormalized(['code' => 'description', 'validations' => ['max_length' => 50]]),
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

    function it_must_have_a_select_column_as_first_column()
    {
        $this->beConstructedThrough('fromColumnDefinitions', [[
            NumberColumn::fromNormalized(['code' => 'quantity']),
            SelectColumn::fromNormalized(['code' => 'ingredient']),
        ]]);

        $this->shouldThrow(new \InvalidArgumentException('The first column should have "select" type'))
            ->duringInstantiation();
    }
}
