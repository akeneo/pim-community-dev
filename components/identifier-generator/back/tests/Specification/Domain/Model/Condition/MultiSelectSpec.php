<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\MultiSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectSpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_is_a_multi_select(): void
    {
        $this->shouldImplement(ConditionInterface::class);
        $this->shouldBeAnInstanceOf(MultiSelect::class);
    }

    public function it_cant_be_instanciated_with_invalid_type()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'bad',
            'operator' => 'EMPTY',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_no_attribute_code_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'operator' => 'EMPTY',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_attribute_code_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'operator' => 'EMPTY',
            'attributeCode' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_scope_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'operator' => 'EMPTY',
            'attributeCode' => 'color',
            'scope' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_locale_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'operator' => 'EMPTY',
            'attributeCode' => 'color',
            'locale' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_no_operator_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_operator_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_operator_is_invalid()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'UNKNOWN',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_not_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_not_an_array()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => 'red',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_not_an_array_of_strings()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => ['red', true],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_empty_for_in_operator()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => [],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cant_be_instanciated_if_value_is_defined_and_operator_is_empty()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
            'value' => ['red', 'blue'],
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_can_be_normalized_with_value_and_in_operator()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => ['red', 'blue'],
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => ['red', 'blue']
        ]);
    }

    public function it_can_be_normalized_without_value_and_empty_operator()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
        ]);
    }

    public function it_can_be_normalized_with_scope_and_locale()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'multi_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]);
    }
}
