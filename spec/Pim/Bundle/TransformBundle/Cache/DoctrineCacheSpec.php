<?php

namespace spec\Pim\Bundle\TransformBundle\Cache;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineCacheSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $doctrine,
        ObjectManager $manager
    ) {
        $doctrine->getManagerForClass(Argument::any())->willReturn($manager);

        $this->beConstructedWith($doctrine);
    }

    function it_finds_an_object_by_code_using_its_repository_for_the_first_time(
        ObjectManager $manager,
        ObjectRepository $repository,
        \stdClass $object
    ) {
        $repository->implement('Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface');
        $manager->getRepository('Object\\Class')->willReturn($repository);
        $repository->findOneByIdentifier('foo')->willReturn($object);

        $this->find('Object\\Class', 'foo')->shouldReturn($object);
    }

    function it_fetches_the_object_from_the_in_memory_cache_the_second_time(
        ObjectManager $manager,
        ObjectRepository $repository,
        \stdClass $object
    ) {
        $repository->implement('Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface');
        $manager->getRepository('Object\\Class')->willReturn($repository);
        $repository->findOneByIdentifier('foo')->shouldBeCalledTimes(1)->willReturn($object);

        $this->find('Object\\Class', 'foo')->shouldReturn($object);
        $this->find('Object\\Class', 'foo')->shouldReturn($object);
        $this->find('Object\\Class', 'foo')->shouldReturn($object);
    }

    function it_finds_an_object_in_the_reference_repository_if_it_has_been_set(
        ReferenceRepository $repository,
        \stdClass $object
    ) {
        $this->setReferenceRepository($repository);
        $repository->hasReference('Object\\Class.foo')->willReturn(true);
        $repository->getReference('Object\\Class.foo')->willReturn($object);

        $this->find('Object\\Class', 'foo')->shouldReturn($object);
    }
}
