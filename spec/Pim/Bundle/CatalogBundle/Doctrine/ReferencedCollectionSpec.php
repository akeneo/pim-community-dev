<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReferencedCollectionSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ObjectRepository $repository,
        ClassMetadata $classMetadata
    ) {
        $objectManager->getRepository('MyItemClass')->willReturn($repository);
        $objectManager->getClassMetadata('MyItemClass')->willReturn($classMetadata);

        $this->beConstructedWith('MyItemClass', [4, 8, 15], $objectManager);
    }

    function it_is_a_collection()
    {
        $this->shouldImplement('Doctrine\Common\Collections\Collection');
    }

    function it_holds_an_initialization_state()
    {
        $this->shouldNotBeInitialized();
        $this->setInitialized(true);
        $this->shouldBeInitialized();
    }

    function it_loads_entities_whenever_trying_to_access_the_collection(
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        EntityStub $entity4,
        EntityStub $entity8,
        EntityStub $entity15
    ) {
        $classMetadata->getIdentifier()->willReturn(['id']);
        $repository->findBy(['id' => [4, 8, 15]])->willReturn([$entity4, $entity8, $entity15]);

        $this->toArray()->shouldReturn([$entity4, $entity8, $entity15]);
    }

    function it_throws_exception_when_entity_class_uses_a_composite_key(
        ObjectRepository $repository,
        ClassMetadata $classMetadata
    ) {
        $classMetadata->getIdentifier()->willReturn(['id', 'code']);

        $exception = new \LogicException('The configured entity uses a composite key which is not supported by the collection');
        $this->shouldThrow($exception)->duringToArray();
    }
}

class EntityStub
{
}
