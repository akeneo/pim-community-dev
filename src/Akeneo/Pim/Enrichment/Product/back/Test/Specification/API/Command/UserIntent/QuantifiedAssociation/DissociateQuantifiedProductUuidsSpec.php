<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DissociateQuantifiedProductUuidsSpec extends ObjectBehavior
{
    private UuidInterface $uuid1;
    private UuidInterface $uuid2;
    function let()
    {
        $this->uuid1 = Uuid::uuid4();
        $this->uuid2 = Uuid::uuid4();

        $this->beConstructedWith('X_SELL', [$this->uuid1, $this->uuid2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DissociateQuantifiedProductUuids::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_product_uuids()
    {
        $this->productUuids()->shouldReturn([$this->uuid1, $this->uuid2]);
    }

    function it_can_only_be_instantiated_with_uuid_product_uuids()
    {
        $this->beConstructedWith('X_SELL', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_product_uuids()
    {
        $this->beConstructedWith('X_SELL', []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_empty_association_type()
    {
        $this->beConstructedWith('', [$this->uuid1, $this->uuid2]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
