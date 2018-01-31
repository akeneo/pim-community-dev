<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

class AttributeGroupUpdateGuesserSpec extends ObjectBehavior
{
    function let(EntityManager $em, UnitOfWork $uow)
    {
        $em->getUnitOfWork()->willReturn($uow);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\UpdateGuesser\AttributeGroupUpdateGuesser');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_update_action()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_guesses_attribute_group_updates(
        $em,
        $uow,
        AttributeInterface $attribute,
        AttributeGroupInterface $group
    ) {
        $uow->getEntityChangeSet($attribute)->willReturn(['group' => [$group]]);
        $this->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$attribute, $group]);
    }

    function it_returns_no_pending_updates_if_not_given_an_attribute($em)
    {
        $this->guessUpdates($em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }

    function it_returns_no_pending_attribute_group_updates_if_attribute_group_has_not_been_changed(
        $em,
        $uow,
        AttributeInterface $attribute
    ) {
        $uow->getEntityChangeSet($attribute)->willReturn(null);
        $this->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([$attribute]);
    }
}
