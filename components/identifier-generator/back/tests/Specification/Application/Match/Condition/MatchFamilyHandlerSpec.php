<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

class MatchFamilyHandlerSpec extends ObjectBehavior
{
    public function it_should_support_only_family_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(Family::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_family_condition(): void
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
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'EMPTY',
            ]),
            new ProductProjection(true, null, [], [])
        )->shouldReturn(true);
    }

    public function it_should_not_match_empty()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'EMPTY',
            ]),
            new ProductProjection(true, 'familyCode', [], [])
        )->shouldReturn(false);
    }

    public function it_should_match_not_empty()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'NOT EMPTY',
            ]),
            new ProductProjection(true, 'familyCode', [], [])
        )->shouldReturn(true);
    }

    public function it_should_not_match_not_empty()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'NOT EMPTY',
            ]),
            new ProductProjection(true, null, [], [])
        )->shouldReturn(false);
    }

    public function it_should_match_in()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'IN',
                'value' => ['shirts', 'jeans'],
            ]),
            new ProductProjection(true, 'shirts', [], [])
        )->shouldReturn(true);
    }

    public function it_should_not_match_in()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'IN',
                'value' => ['shirts', 'jeans'],
            ]),
            new ProductProjection(true, 'jackets', [], [])
        )->shouldReturn(false);
    }

    public function it_should_match_not_in()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'NOT IN',
                'value' => ['shirts', 'jeans'],
            ]),
            new ProductProjection(true, 'jackets', [], [])
        )->shouldReturn(true);
    }

    public function it_should_not_match_not_in()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'NOT IN',
                'value' => ['shirts', 'jeans'],
            ]),
            new ProductProjection(true, 'shirts', [], [])
        )->shouldReturn(false);
    }

    public function it_should_not_match_not_in_when_product_has_no_family()
    {
        $this->__invoke(
            Family::fromNormalized([
                'type' => 'family',
                'operator' => 'NOT IN',
                'value' => ['shirts', 'jeans'],
            ]),
            new ProductProjection(true, null, [], [])
        )->shouldReturn(false);
    }
}
