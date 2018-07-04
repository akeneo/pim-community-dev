<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use PimEnterprise\Component\Security\Model\CategoryAccessInterface;

class CategoryAccessUpdateGuesserSpec extends ObjectBehavior
{
    function it_support_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('an_other_action')->shouldReturn(false);
    }

    function it_add_category_to_pending_on_category_access_update(
        EntityManager $em,
        CategoryAccessInterface $salesTreeAccess,
        CategoryInterface $salesTree
    ) {
        $salesTreeAccess->getCategory()->willReturn($salesTree);
        $this->guessUpdates($em, $salesTreeAccess, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$salesTree]);
    }

    function it_does_not_add_category_to_pending_on_other_entity_update(EntityManager $em)
    {
        $this->guessUpdates($em, new \StdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([]);
    }
}
