<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

class MatchEmptyIdentifierHandlerSpec extends ObjectBehavior
{
    public function it_should_support_only_empty_identifier_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(EmptyIdentifier::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_empty_identifier_condition(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                Enabled::fromBoolean(true),
                new ProductProjection(true, null, [], []),
            ]);
    }

    public function it_should_match_product_without_identifier()
    {
        $this->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [], [])
        )->shouldReturn(true);
    }

    public function it_should_match_product_with_empty_identifier()
    {
        $this->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [
                'sku-<all_channels>-<all_locales>' => ''
            ], [])
        )->shouldReturn(true);
    }

    public function it_should_not_match_product_with_filled_identifier()
    {
        $this->__invoke(
            new EmptyIdentifier('sku'),
            new ProductProjection(true, null, [
                'sku-<all_channels>-<all_locales>' => 'productidentifier'
            ], [])
        )->shouldReturn(false);
    }
}
