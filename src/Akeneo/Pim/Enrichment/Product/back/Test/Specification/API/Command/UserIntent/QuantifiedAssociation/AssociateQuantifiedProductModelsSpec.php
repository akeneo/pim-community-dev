<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;

class AssociateQuantifiedProductModelsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('X_SELL', [new QuantifiedEntity('foo', 5)]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociateQuantifiedProductModels::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
        $this->shouldImplement(UserIntent::class);
    }

    function it_cannot_be_constructed_with_empty_association_type()
    {
        $this->beConstructedWith('', [new QuantifiedEntity('foo', 5)]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_empty_quantified_product_models()
    {
        $this->beConstructedWith('X_SELL', []);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_non_valid_quantified_entities()
    {
        $this->beConstructedWith('X_SELL', [new \stdClass()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_quantified_product_models()
    {
        $this->quantifiedProductModels()->shouldBeLike([new QuantifiedEntity('foo', 5)]);
    }
}
