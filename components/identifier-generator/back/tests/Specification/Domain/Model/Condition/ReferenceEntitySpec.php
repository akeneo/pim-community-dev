<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use PhpSpec\ObjectBehavior;

class ReferenceEntitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntity::class);
    }

    function it_is_a_ref_entity(): void
    {
        $this->shouldImplement(ConditionInterface::class);
        $this->shouldBeAnInstanceOf(ReferenceEntity::class);
    }

    public function it_should_throw_exception_if_type_is_not_ref_entity()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'simple_select',
            'operator' => 'NOT EMPTY'
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_no_attribute_code_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'operator' => 'EMPTY'
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_attribute_code_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_scope_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => 'brand',
            'scope' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_locale_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => 'brand',
            'locale' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_no_operator_is_defined()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_operator_is_not_a_string()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => true,
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_throw_exception_if_operator_is_invalid()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'UNKNOWN',
        ]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_should_normalize()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
        ]);
    }

    public function it_should_normalize_with_scope_and_locale()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]]);
        $this->normalize()->shouldReturn([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US'
        ]);
    }
}
