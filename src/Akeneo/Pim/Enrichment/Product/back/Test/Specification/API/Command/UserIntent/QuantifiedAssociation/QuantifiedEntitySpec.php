<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use PhpSpec\ObjectBehavior;

class QuantifiedEntitySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('foo', 5);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedEntity::class);
    }

    function it_cannot_be_constructed_with_empty_product_identifier()
    {
        $this->beConstructedWith('', 5);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_entity_identifier()
    {
        $this->entityIdentifier()->shouldReturn('foo');
    }

    function it_returns_the_quantity()
    {
        $this->quantity()->shouldReturn(5);
    }
}
