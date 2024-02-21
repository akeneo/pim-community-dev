<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\AttributeGroupUpdateGuesser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class AttributeGroupUpdateGuesserSpec extends ObjectBehavior
{
    function let(EntityManager $em, UnitOfWork $uow)
    {
        $em->getUnitOfWork()->willReturn($uow);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupUpdateGuesser::class);
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

    function it_returns_no_pending_updates_if_not_given_an_attribute($em)
    {
        $this->guessUpdates($em, new \stdClass(), UpdateGuesserInterface::ACTION_UPDATE_ENTITY)
            ->shouldReturn([]);
    }
}
