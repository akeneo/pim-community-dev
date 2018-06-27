<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class FamilyAttributeRequirementUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_an_update_guesser()
    {
        $this->shouldImplement(UpdateGuesserInterface::class);
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_supports_delete_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
    }

    function it_guesses_family_update_when_an_attribute_requirement_is_added(
        EntityManager $em,
        AttributeRequirement $attributeRequirement,
        Family $family
    )
    {
        $attributeRequirement->getFamily()->willReturn($family);

        $this->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$family]);
    }

    function it_guesses_family_update_when_an_attribute_requirement_is_removed(
        EntityManager $em,
        AttributeRequirement $attributeRequirement,
        Family $family
    )
    {
        $attributeRequirement->getFamily()->willReturn($family);

        $this->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_DELETE)
            ->shouldReturn([$family]);
    }

    function it_returns_no_pending_updates_if_not_given_an_attribute_requirement(EntityManager $em)
    {
        $this->guessUpdates($em, new Family(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
