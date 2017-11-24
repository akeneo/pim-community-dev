<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class ObjectDetacherSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_detaches_an_object_from_entity_manager(
        $registry,
        EntityManagerInterface $manager,
        UnitOfWork $uow,
        ClassMetadata $classMetadata
    ) {
        $object = new \stdClass();
        $registry->getManagerForClass('stdClass')->willReturn($manager);
        $manager->getUnitOfWork()->willReturn($uow);
        $manager->getClassMetadata('stdClass')->willReturn($classMetadata);
        $classMetadata->rootEntityName = 'stdClass';

        $manager->detach($object)->shouldBeCalled();

        $this->detach($object);
    }

    function it_detaches_many_objects_from_entity_manager(
        $registry,
        EntityManagerInterface $manager,
        UnitOfWork $uow,
        ClassMetadata $classMetadata
    ) {
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $objects = [$object1, $object2];
        $registry->getManagerForClass('stdClass')->willReturn($manager);
        $manager->getUnitOfWork()->willReturn($uow);
        $manager->getClassMetadata('stdClass')->willReturn($classMetadata);
        $classMetadata->rootEntityName = 'stdClass';

        $manager->detach($object1)->shouldBeCalled();
        $manager->detach($object2)->shouldBeCalled();

        $this->detachAll($objects);
    }
}
