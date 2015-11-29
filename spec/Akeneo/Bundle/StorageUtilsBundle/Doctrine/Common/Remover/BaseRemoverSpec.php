<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseRemoverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, RemovingOptionsResolverInterface $optionsResolver, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher, 'Pim\Component\Catalog\Model\GroupTypeInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface');
    }

    function it_removes_the_object_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupTypeInterface $type)
    {
        $optionsResolver->resolveRemoveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);

        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->remove($type);
    }

    function it_removes_the_objects_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupTypeInterface $type1, GroupTypeInterface $type2)
    {
        $optionsResolver->resolveRemoveAllOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);

        $optionsResolver->resolveRemoveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false]);

        $objectManager->remove($type1)->shouldBeCalled();
        $objectManager->remove($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->removeAll([$type1, $type2]);
    }

    function it_removes_the_object_and_does_not_flush($objectManager, $optionsResolver, GroupTypeInterface $type)
    {
        $optionsResolver->resolveRemoveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);

        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->remove($type, ['flush' => false]);
    }

    function it_removes_the_objects_and_does_not_flush($objectManager, $optionsResolver, GroupTypeInterface $type1, GroupTypeInterface $type2)
    {
        $optionsResolver->resolveRemoveAllOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);

        $optionsResolver->resolveRemoveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false]);

        $objectManager->remove($type1)->shouldBeCalled();
        $objectManager->remove($type2)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->removeAll([$type1, $type2], ['flush' => false]);
    }

    function it_throws_exception_when_remove_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "Pim\Component\Catalog\Model\GroupTypeInterface", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('remove', [$anythingElse]);
        $this->shouldThrow($exception)->during('removeAll', [[$anythingElse, $anythingElse]]);
    }
}
