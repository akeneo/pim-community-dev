<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

class MatchSimpleSelectHandlerSpec extends ObjectBehavior
{
    public function it_should_support_only_simple_select_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(SimpleSelect::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_simple_select_condition(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                new EmptyIdentifier('sku'),
                new ProductProjection(true, null, [], []),
            ]);
    }

    public function it_should_match_empty()
    {
        $condition = SimpleSelect::fromNormalized([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'operator' => 'EMPTY',
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'red',
        ], []))->shouldReturn(false);
    }

    public function it_should_match_not_empty()
    {

        $condition = SimpleSelect::fromNormalized([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'operator' => 'NOT EMPTY',
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'red',
        ], []))->shouldReturn(true);
    }

    public function it_should_match_in_list()
    {
        $condition = SimpleSelect::fromNormalized([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'operator' => 'IN',
            'value' => ['red', 'pink']
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'red',
        ], []))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'blue',
        ], []))->shouldReturn(false);
    }

    public function it_should_match_not_in_list()
    {
        $condition = SimpleSelect::fromNormalized([
            'type' => 'simple_select',
            'attributeCode' => 'color',
            'operator' => 'NOT IN',
            'value' => ['red', 'pink']
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'red',
        ], []))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'color-<all_channels>-<all_locales>' => 'blue',
        ], []))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
    }
}
