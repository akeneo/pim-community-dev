<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class AssociationUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_supports_entity_updates_and_deletion()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_products_as_updated_when_an_association_is_updated_or_removed(
        EntityManager $em,
        ProductInterface $foo,
        AssociationInterface $association
    ) {
        $association->getOwner()->willReturn($foo);
        $this->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$foo]);
        $this->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$foo]);
    }
}
