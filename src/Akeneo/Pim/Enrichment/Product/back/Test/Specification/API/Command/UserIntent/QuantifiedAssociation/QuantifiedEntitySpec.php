<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use PhpSpec\ObjectBehavior;

class QuantifiedEntitySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo', 5);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedEntity::class);
    }

    public function it_cannot_be_constructed_with_empty_product_identifier()
    {
        $this->beConstructedWith('', 5);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_negative_quantity()
    {
        $this->beConstructedWith('foo', -5);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_zero_quantity()
    {
        $this->beConstructedWith('foo', 0);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_entity_identifier()
    {
        $this->entityIdentifier()->shouldReturn('foo');
    }

    public function it_returns_the_quantity()
    {
        $this->quantity()->shouldReturn(5);
    }
}
