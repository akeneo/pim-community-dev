<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\UserBundle\Context\UserContext;

class FamilyManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepository $repository,
        UserContext $userContext,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $repository,
            $userContext,
            $objectManager,
            $eventDispatcher
        );
    }

    function it_should_return_a_choice_list($userContext, $repository)
    {
        $userContext->getCurrentLocaleCode()->willReturn('foo');
        $repository->getChoices(['localeCode' => 'foo'])->willReturn(['foo' => 'foo']);

        $this->getChoices()->shouldReturn(['foo' => 'foo']);
    }

    function it_should_dispatch_an_event_when_remove_a_family(
        $eventDispatcher,
        $objectManager,
        Family $family
    ) {
        $eventDispatcher->dispatch(
            CatalogEvents::PRE_REMOVE_FAMILY,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($family);
    }
}
