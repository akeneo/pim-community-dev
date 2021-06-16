<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Factory\TableValueFactory;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class TableValueFactorySpec extends ObjectBehavior
{
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

    function it_throws_an_exception_if_cell_is_not_a_string()
    {
        $attribute = $this->buildTableAttribute(false, false);

        $this->shouldThrow(InvalidPropertyTypeException::validArrayStructureExpected(
            'nutrition',
            'TODO cell should be a scalar',
            TableValueFactory::class,
            [['foo' => ['an array']]]
        ))->during(
            'createByCheckingData', [
                $attribute,
                null,
                null,
                [['foo' => ['an array']]]
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
