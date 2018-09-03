<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseRemoverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            ModelToRemove::class
        );
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType(RemoverInterface::class);
        $this->shouldHaveType(BulkRemoverInterface::class);
    }

    function it_removes_the_object_and_flushes_the_unit_of_work($objectManager, ModelToRemove $type)
    {
        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->remove($type);
    }

    function it_removes_the_objects_and_flushes_the_unit_of_work($objectManager, ModelToRemove $type1, ModelToRemove $type2)
    {
        $objectManager->remove($type1)->shouldBeCalled();
        $objectManager->remove($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->removeAll([$type1, $type2]);
    }

    function it_throws_exception_when_remove_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                ModelToRemove::class,
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('remove', [$anythingElse]);
        $this->shouldThrow($exception)->during('removeAll', [[$anythingElse, $anythingElse]]);
    }
}

class ModelToRemove {
    public function getId() {
        return 42;
    }
}
