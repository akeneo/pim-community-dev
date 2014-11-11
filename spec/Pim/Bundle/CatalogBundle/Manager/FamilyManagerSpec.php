<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Pim\Bundle\CatalogBundle\Event\FamilyEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepository $repository,
        UserContext $userContext,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessManager $completenessManager
    ) {
        $this->beConstructedWith(
            $repository,
            $userContext,
            $objectManager,
            $eventDispatcher,
            $completenessManager
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
    }

    function it_provides_a_choice_list($userContext, $repository)
    {
        $userContext->getCurrentLocaleCode()->willReturn('foo');
        $repository->getChoices(['localeCode' => 'foo'])->willReturn(['foo' => 'foo']);

        $this->getChoices()->shouldReturn(['foo' => 'foo']);
    }

    function it_schedule_completeness_when_save_a_family(FamilyInterface $family, $completenessManager, $objectManager)
    {
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->scheduleForFamily($family)->shouldBeCalled();
        $this->save($family);
    }

    function it_throws_exception_when_save_anything_else_than_a_family()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }

    function it_throws_exception_when_remove_anything_else_than_a_family()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\FamilyInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('remove', [$anythingElse]);
    }

    function it_dispatches_an_event_when_removing_a_family(
        $eventDispatcher,
        $objectManager,
        FamilyInterface $family
    ) {
        $eventDispatcher->dispatch(
            FamilyEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($family);
    }
}
