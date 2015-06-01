<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;

class ObjectDetacherSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_detaches_an_object_from_object_manager($registry, ObjectManager $manager)
    {
        $object = new \stdClass();
        $registry->getManagerForClass('stdClass')
            ->shouldBeCalled()
            ->willReturn($manager);

        $manager->detach($object)->shouldBeCalled();

        $this->detach($object);
    }

    function it_detaches_many_objects_from_object_manager($registry, ObjectManager $manager)
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $objects = [$object1, $object2];
        $registry->getManagerForClass('stdClass')
            ->shouldBeCalled()
            ->willReturn($manager);

        $manager->detach($object1)->shouldBeCalled();
        $manager->detach($object2)->shouldBeCalled();

        $this->detachAll($objects);
    }
}
