<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

class MatchEnabledHandlerSpec extends ObjectBehavior
{
    public function it_should_support_only_enabled_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(Enabled::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_enabled_condition(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                new EmptyIdentifier('sku'),
                new ProductProjection(true, null, [], []),
            ]);
    }

    public function it_matches_only_enabled_products(): void
    {
        $this->__invoke(
            Enabled::fromBoolean(true),
            new ProductProjection(true, '', [], [])
        )->shouldReturn(true);

        $this->__invoke(
            Enabled::fromBoolean(true),
            new ProductProjection(false, '', [], [])
        )->shouldReturn(false);
    }
}
