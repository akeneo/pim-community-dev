<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class AssociateProductUuidsSpec extends ObjectBehavior
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
        $this->shouldHaveType(AssociateProductUuids::class);
        $this->shouldImplement(AssociationUserIntent::class);
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_product_uuids()
    {
        $this->productUuids()->shouldReturn([$this->uuid1, $this->uuid2]);
    }

    function it_can_only_be_instantiated_with_product_uuids()
    {
        $this->beConstructedWith('X_SELL', ['uuid', 1012, false]);
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
