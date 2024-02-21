<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

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
        UnitOfWork $uo,
        AttributeRequirement $attributeRequirement,
        Family $family
    )
    {
        $em->getUnitOfWork()->willReturn($uo);
        $uo->getEntityState($family)->willReturn(UnitOfWork::STATE_MANAGED);

        $attributeRequirement->getFamily()->willReturn($family);

        $this->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$family]);
    }

    function it_guesses_family_update_when_an_attribute_requirement_is_removed(
        EntityManager $em,
        UnitOfWork $uo,
        AttributeRequirement $attributeRequirement,
        Family $family
    )
    {
        $em->getUnitOfWork()->willReturn($uo);
        $uo->getEntityState($family)->willReturn(UnitOfWork::STATE_MANAGED);

        $attributeRequirement->getFamily()->willReturn($family);

        $this->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_DELETE)
            ->shouldReturn([$family]);
    }

    function it_returns_no_pending_update_if_family_is_deleted_too(
        EntityManager $em,
        UnitOfWork $uo,
        AttributeRequirement $attributeRequirement,
        Family $family
    )
    {
        $em->getUnitOfWork()->willReturn($uo);
        $uo->getEntityState($family)->willReturn(UnitOfWork::STATE_REMOVED);
        $attributeRequirement->getFamily()->willReturn($family);

        $this->guessUpdates($em, $attributeRequirement, UpdateGuesserInterface::ACTION_DELETE)
            ->shouldReturn([]);
    }

    function it_returns_no_pending_updates_if_not_given_an_attribute_requirement(EntityManager $em)
    {
        $this->guessUpdates($em, new Family(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
