<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

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
