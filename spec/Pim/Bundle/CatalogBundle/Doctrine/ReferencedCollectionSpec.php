<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\UnitOfWork as MongoDBODMUnitOfWork;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;

class ReferencedCollectionSpec extends ObjectBehavior
{
    function let(
        EntityManager $em,
        DocumentManager $dm,
        ManagerRegistry $registry,
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        MongoDBODMUnitOfWork $uow
    ) {
        $registry->getManagerForClass(Argument::type('string'))->will(function ($args) use ($em, $dm) {
            return 'MyItemClass' === $args[0] ? $em : $dm;
        });
        $registry->getRepository('MyItemClass')->willReturn($repository);
        $em->getClassMetadata('MyItemClass')->willReturn($classMetadata);
        $dm->getUnitOfWork()->willReturn($uow);

        $this->beConstructedWith('MyItemClass', [4, 8, 15], $registry);
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

    function it_schedules_owning_document_for_update_when_adding_element_to_the_collection(
        MongoDBODMUnitOfWork $uow,
        DocumentStub $document,
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        EntityStub $entity4,
        EntityStub $entity8,
        EntityStub $entity15,
        EntityStub $newEntity
    ) {
        $classMetadata->getIdentifier()->willReturn(['id']);
        $repository->findBy(['id' => [4, 8, 15]])->willReturn([$entity4, $entity8, $entity15]);

        $uow->getDocumentState($document)->willReturn(MongoDBODMUnitOfWork::STATE_MANAGED);
        $uow->isScheduledForUpdate($document)->willReturn(false);
        $uow->scheduleForUpdate($document)->shouldBeCalled();

        $this->setOwner($document);
        $this->add($newEntity);
    }

    function it_schedules_owning_document_for_update_when_removing_element_from_the_collection(
        MongoDBODMUnitOfWork $uow,
        DocumentStub $document,
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        EntityStub $entity4,
        EntityStub $entity8,
        EntityStub $entity15
    ) {
        $classMetadata->getIdentifier()->willReturn(['id']);
        $repository->findBy(['id' => [4, 8, 15]])->willReturn([$entity4, $entity8, $entity15]);

        $uow->getDocumentState($document)->willReturn(MongoDBODMUnitOfWork::STATE_MANAGED);
        $uow->isScheduledForUpdate($document)->willReturn(false);
        $uow->scheduleForUpdate($document)->shouldBeCalled();

        $this->setOwner($document);
        $this->removeElement($entity4);
    }

    function it_schedules_owning_document_for_update_when_removing_element_by_key_from_the_collection(
        MongoDBODMUnitOfWork $uow,
        DocumentStub $document,
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        EntityStub $entity4,
        EntityStub $entity8,
        EntityStub $entity15
    ) {
        $classMetadata->getIdentifier()->willReturn(['id']);
        $repository->findBy(['id' => [4, 8, 15]])->willReturn([$entity4, $entity8, $entity15]);

        $uow->getDocumentState($document)->willReturn(MongoDBODMUnitOfWork::STATE_MANAGED);
        $uow->isScheduledForUpdate($document)->willReturn(false);
        $uow->scheduleForUpdate($document)->shouldBeCalled();

        $this->setOwner($document);
        $this->remove(0);
    }

    function it_schedules_owning_document_for_update_when_setting_element_by_key_in_the_collection(
        MongoDBODMUnitOfWork $uow,
        DocumentStub $document,
        ObjectRepository $repository,
        ClassMetadata $classMetadata,
        EntityStub $entity4,
        EntityStub $entity8,
        EntityStub $entity15,
        EntityStub $newEntity
    ) {
        $classMetadata->getIdentifier()->willReturn(['id']);
        $repository->findBy(['id' => [4, 8, 15]])->willReturn([$entity4, $entity8, $entity15]);

        $uow->getDocumentState($document)->willReturn(MongoDBODMUnitOfWork::STATE_MANAGED);
        $uow->isScheduledForUpdate($document)->willReturn(false);
        $uow->scheduleForUpdate($document)->shouldBeCalled();

        $this->setOwner($document);
        $this->set(2, $newEntity);
    }
}

class EntityStub
{
}

class DocumentStub
{
}
