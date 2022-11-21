<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use PhpSpec\ObjectBehavior;

class DissociateProductsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('X_SELL', ['identifier1', 'identifier2']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DissociateProducts::class);
        $this->shouldImplement(AssociationUserIntent::class);
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_product_identifiers()
    {
        $this->productIdentifiers()->shouldReturn(['identifier1', 'identifier2']);
    }

    function it_can_only_be_instantiated_with_string_product_identifiers()
    {
        $this->beConstructedWith('X_SELL', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_product_identifiers()
    {
        $this->beConstructedWith('X_SELL', []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_if_one_of_the_product_identifiers_is_empty()
    {
        $this->beConstructedWith('X_SELL', ['a', '', 'b']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_association_type()
    {
        $this->beConstructedWith('', ['identifier1', 'identifier2']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
