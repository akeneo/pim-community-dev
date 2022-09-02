<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use PhpSpec\ObjectBehavior;

class DissociateQuantifiedProductModelsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('X_SELL', ['identifier1', 'identifier2']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DissociateQuantifiedProductModels::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_product_model_codes()
    {
        $this->productModelCodes()->shouldReturn(['identifier1', 'identifier2']);
    }

    function it_can_only_be_instantiated_with_string_product_model_codes()
    {
        $this->beConstructedWith('X_SELL', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_product_model_codes()
    {
        $this->beConstructedWith('X_SELL', []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_if_one_of_the_product_model_codes_is_empty()
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
