<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;

class BaseSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, SavingOptionsResolverInterface $optionsResolver)
    {
        $this->beConstructedWith($objectManager, $optionsResolver, 'Pim\Bundle\CatalogBundle\Model\GroupTypeInterface');
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_persists_the_object_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupTypeInterface $type)
    {
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($type);
    }

    function it_persists_the_objects_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupTypeInterface $type1, GroupTypeInterface $type2)
    {
        $optionsResolver->resolveSaveAllOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false]);

        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->saveAll([$type1, $type2]);
    }

    function it_persists_the_object_and_does_not_flush($objectManager, $optionsResolver, GroupTypeInterface $type)
    {
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($type, ['flush' => false]);
    }

    function it_persists_the_objects_and_does_not_flush($objectManager, $optionsResolver, GroupTypeInterface $type1, GroupTypeInterface $type2)
    {
        $optionsResolver->resolveSaveAllOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false]);

        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->saveAll([$type1, $type2], ['flush' => false]);
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "Pim\Bundle\CatalogBundle\Model\GroupTypeInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
