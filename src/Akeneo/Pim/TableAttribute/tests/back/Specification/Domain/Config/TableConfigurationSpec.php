<?php

namespace Specification\Akeneo\Pim\TableAttribute\Domain\Config;

use Akeneo\Pim\TableAttribute\Domain\Config\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\Config\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\Config\ValueObject\ColumnCode;
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

    function it_cannot_have_the_same_column_twice(ColumnDefinition $definition)
    {
        $definition->code()->willReturn(ColumnCode::fromString('ingredients'));
        $this->beConstructedThrough('fromColumnDefinitions', [[$definition, $definition]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_is_initializable(ColumnDefinition $ingredients, ColumnDefinition $quantity)
    {
        $ingredients->code()->willReturn(ColumnCode::fromString('ingredients'));
        $quantity->code()->willReturn(ColumnCode::fromString('quantity'));
        $this->beConstructedThrough('fromColumnDefinitions', [[$ingredients, $quantity]]);
        $this->shouldHaveType(TableConfiguration::class);
    }

//    TODO: implement when select columns are implemented
//    function it_must_have_a_select_column_as_first_column()
//    {
//        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
//    }
}
