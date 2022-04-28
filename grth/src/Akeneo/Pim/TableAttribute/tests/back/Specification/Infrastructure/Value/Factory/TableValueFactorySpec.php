<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\TableValueFactory;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class TableValueFactorySpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $this->beConstructedWith(
            $tableConfigurationRepository,
            $selectOptionCollectionRepository,
            $getExistingRecordCodes
        );

        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
        ]));

        $selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredient'))
            ->willReturn(SelectOptionCollection::fromNormalized([
                ['code' => 'salt'],
                ['code' => 'sugAR'],
            ]));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValueFactory::class);
    }

    function it_creates_without_checking_data()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [['foo' => 'bar']]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([['foo' => 'bar']]));
    }

    function it_replaces_codes_by_ids()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [
                ['quantity' => 5, 'ingrediENT' => 'salt'],
                ['quantity' => 10, 'ingredient' => 'sugAR'],
            ]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
            [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
        ]));
    }

    function it_does_not_replace_when_column_is_unknown()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [
                ['quantity' => 5, 'ingrediENT' => 'salt'],
                ['quantity' => 10, 'ingredient' => 'sugAR', 'unknown' => 12],
            ]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
            [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR', 'unknown' => 12],
        ]));
    }

    function it_does_not_replace_when_data_is_coming_from_storage()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [
                [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
                [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
            ]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
            [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
        ]));
    }

    function it_removes_duplicate_on_first_column()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [
                ['quantity' => 5, 'ingrediENT' => 'SAlt'],
                ['quantity' => 10, 'ingredient' => 'sugAR'],
                ['quantity' => 20, 'INGredient' => 'SALT'],
                ['quantity' => 30, 'ingredient' => 'salt'],
            ]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
            [ColumnIdGenerator::quantity() => 30, ColumnIdGenerator::ingredient() => 'salt'],
        ]));
    }

    function it_sanitizes_select_option_codes_and_record_codes(
        TableConfigurationRepository $tableConfigurationRepository,
        GetExistingRecordCodes $getExistingRecordCodes
    ) {
        $attribute = $this->buildTableAttribute(false, false);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ReferenceEntityColumn::fromNormalized([
                'id' => ColumnIdGenerator::supplier(),
                'code' => 'supplier',
                'reference_entity_identifier' => 'brand',
            ])
        ]));
        $getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes(['brand' => ['AKENeo']])
            ->willReturn(['brand' => ['Akeneo']]);

        $value = $this->createWithoutCheckingData(
            $attribute,
            null,
            null,
            [
                [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
                [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
                [ColumnIdGenerator::quantity() => 15, ColumnIdGenerator::supplier() => 'AKENeo'],
            ]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 5, ColumnIdGenerator::ingredient() => 'salt'],
            [ColumnIdGenerator::quantity() => 10, ColumnIdGenerator::ingredient() => 'sugAR'],
            [ColumnIdGenerator::quantity() => 15, ColumnIdGenerator::supplier() => 'Akeneo'],
        ]));
    }

    function it_throws_an_exception_if_data_is_not_an_array()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'nutrition',
            TableValueFactory::class,
            'a string'
        ))->during(
            'createByCheckingData', [
                $attribute,
                null,
                null,
                'a string'
            ]
        );
    }

    function it_throws_an_exception_if_row_is_not_an_array()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $this->shouldThrow(InvalidPropertyTypeException::arrayOfArraysExpected(
            'nutrition',
            TableValueFactory::class,
            ['a string']
        ))->during(
            'createByCheckingData', [
                $attribute,
                null,
                null,
                ['a string']
            ]
        );
    }

    function it_throws_an_exception_if_cell_has_not_a_valid_format()
    {
        $attribute = $this->buildTableAttribute(false, false);
        $value = new \stdClass();

        $this->shouldThrow(InvalidPropertyTypeException::validArrayStructureExpected(
            'nutrition',
            'The cell value must be a text string, a number, a boolean or an array.',
            TableValueFactory::class,
            [['foo' => $value]]
        ))->during(
            'createByCheckingData', [
                $attribute,
                null,
                null,
                [['foo' => $value]]
            ]
        );
    }
    function it_filters_empty_cells()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $value = $this->createByCheckingData($attribute,
            null,
            null,
            [['foo' => '', 'bar' => 'baz', 'toto' => null]]
        );
        $value->shouldBeAnInstanceOf(TableValue::class);
        $value->getData()->shouldBeLike(Table::fromNormalized([['bar' => 'baz']]));
    }

    private function buildTableAttribute(bool $isLocalizable = false, bool $isScopable = false): Attribute
    {
        return new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            $isLocalizable,
            $isScopable,
            null,
            null,
            null,
            AttributeTypes::BACKEND_TYPE_TABLE,
            []
        );
    }
}
