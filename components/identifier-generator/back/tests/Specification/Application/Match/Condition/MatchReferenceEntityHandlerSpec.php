<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

class MatchReferenceEntityHandlerSpec extends ObjectBehavior
{
    public function it_should_support_only_reference_entity_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(ReferenceEntity::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_reference_entity_condition(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                new EmptyIdentifier('sku'),
                new ProductProjection(true, null, [], []),
            ]);
    }

    public function it_should_match_not_empty()
    {
        $condition = ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [
            'brand-<all_channels>-<all_locales>' => 'akeneo',
        ], []))->shouldReturn(true);
    }
}
