<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AssociationsUpdateGuesser;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;

class AssociationsUpdateGuesserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['stdClass']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationsUpdateGuesser::class);
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_guesses_associations_updates(
        AssociationInterface $association,
        EntityWithAssociationsInterface $owner,
        EntityManager $em
    ) {
        $association->getOwner()->willReturn($owner);
        $this->guessUpdates($em, $association, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$owner]);
    }

    function it_returns_no_pending_updates_if_not_given_association_interface(
        EntityManager $em,
        LocaleInterface $locale
    ) {
        $this->guessUpdates($em, $locale, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
