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
}
