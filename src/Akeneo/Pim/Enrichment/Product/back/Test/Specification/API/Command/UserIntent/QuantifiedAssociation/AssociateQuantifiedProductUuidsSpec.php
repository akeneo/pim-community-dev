<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProductWithUuid;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AssociateQuantifiedProductUuidsSpec extends ObjectBehavior
{
    private UuidInterface $uuid;
    function let()
    {
        $this->uuid = Uuid::uuid4();
        $this->beConstructedWith('X_SELL', [new QuantifiedProductWithUuid($this->uuid, 5)]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociateQuantifiedProductUuids::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
        $this->shouldImplement(UserIntent::class);
    }

    function it_cannot_be_constructed_with_empty_association_type()
    {
        $this->beConstructedWith('', [new QuantifiedProductWithUuid($this->uuid, 5)]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_empty_quantified_products()
    {
        $this->beConstructedWith('X_SELL', []);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_non_valid_quantified_products()
    {
        $this->beConstructedWith('X_SELL', [new \stdClass()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_quantified_products()
    {
        $this->quantifiedProducts()->shouldBeLike([new QuantifiedProductWithUuid($this->uuid, 5)]);
    }
}
