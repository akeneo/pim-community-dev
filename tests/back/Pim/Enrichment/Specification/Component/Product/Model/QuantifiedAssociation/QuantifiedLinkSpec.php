<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedLinkSpec extends ObjectBehavior
{
    function it_is_created_with_an_entity_identifier_and_quantity()
    {
        $entityWithAssociationIdentifier = 'entity_with_association_identifier';
        $quantity = 10;

        $this->beConstructedThrough('fromIdentifier', [$entityWithAssociationIdentifier, $quantity]);

        $this->normalize()->shouldReturn([
            'identifier' => $entityWithAssociationIdentifier,
            'quantity'=> $quantity
        ]);
    }

    function it_returns_the_identifier_of_the_link()
    {
        $entityWithAssociationIdentifier = 'entity_with_association_identifier';

        $this->beConstructedThrough('fromIdentifier', [$entityWithAssociationIdentifier, 10]);

        $this->identifier()->shouldReturn($entityWithAssociationIdentifier);
    }

    function it_throws_if_the_identifier_is_empty()
    {
        $emptyIdentifier = '';

        $this->beConstructedThrough('fromIdentifier', [$emptyIdentifier, 1]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_is_created_with_a_uuid()
    {
        $uuid = Uuid::uuid4();
        $this->beConstructedThrough('fromUuid', [$uuid->toString(), 'an_identifier', 10]);

        $this->normalize()->shouldReturn([
            'uuid' => $uuid->toString(),
            'identifier' => 'an_identifier',
            'quantity' => 10,
        ]);
    }

    function it_can_be_created_without_identifier()
    {
        $uuid = Uuid::uuid4();
        $this->beConstructedThrough('fromUuid', [$uuid->toString(), null, 10]);

        $this->normalize()->shouldReturn([
            'uuid' => $uuid->toString(),
            'identifier' => null,
            'quantity' => 10,
        ]);
    }
}
