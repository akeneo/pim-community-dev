<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class AssociationUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
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
