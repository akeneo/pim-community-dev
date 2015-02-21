<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\CompletenessSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

class FamilySaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        CompletenessSavingOptionsResolver $optionsResolver
    ) {
        $this->beConstructedWith($objectManager, $completenessManager, $optionsResolver);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_saves_a_family_and_flushes_by_default($objectManager, $optionsResolver, FamilyInterface $family)
    {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => true]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($family);
    }

    function it_saves_a_family_and_does_not_flushe($objectManager, $optionsResolver, FamilyInterface $family)
    {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false, 'schedule' => true]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($family, ['flush' => false]);
    }

    function it_saves_a_family_and_does_not_schedule(
        $completenessManager,
        $optionsResolver,
        $objectManager,
        FamilyInterface $family
    ) {
        $family->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['schedule' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => false]);
        $objectManager->persist($family)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->scheduleForFamily($family)->shouldNotBeCalled($family);
        $this->save($family, ['schedule' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
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
}
