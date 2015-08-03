<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Model\LocaleAccessInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class LocaleAccessUpdateGuesserSpec extends ObjectBehavior
{
    function it_support_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('an_other_action')->shouldReturn(false);
    }

    function it_add_locale_to_pending_on_locale_access_update(
        EntityManager $em,
        LocaleAccessInterface $enAccess,
        LocaleInterface $en
    ) {
        $enAccess->getLocale()->willReturn($en);
        $this->guessUpdates($em, $enAccess, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$en]);
    }

    function it_does_not_add_locale_to_pending_on_other_entity_update(EntityManager $em)
    {
        $this->guessUpdates($em, new \StdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([]);
    }
}
