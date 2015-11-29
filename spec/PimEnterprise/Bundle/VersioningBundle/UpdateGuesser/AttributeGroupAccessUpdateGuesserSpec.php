<?php

namespace spec\PimEnterprise\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class AttributeGroupAccessUpdateGuesserSpec extends ObjectBehavior
{
    function it_support_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('an_other_action')->shouldReturn(false);
    }

    function it_add_attribute_group_to_pending_on_attribute_group_access_update(
        EntityManager $em,
        AttributeGroupAccessInterface $marketingAccess,
        AttributeGroupInterface $marketing
    ) {
        $marketingAccess->getAttributeGroup()->willReturn($marketing);
        $this->guessUpdates($em, $marketingAccess, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$marketing]);
    }

    function it_does_not_add_attribute_group_to_pending_on_other_entity_update(EntityManager $em)
    {
        $this->guessUpdates($em, new \StdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([]);
    }
}
