<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollectionFactory;
use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;
use Doctrine\ODM\MongoDB\Event\PreFlushEventArgs;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\Common\Collections\Collection;

/**
 * @require Doctrine\ODM\MongoDB\Events
 * @require Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\Mapping\ClassMetadata
 */
class EntitiesTypeSubscriberSpec extends ObjectBehavior
{
    function let(ReferencedCollectionFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_is_an_event_subsriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad', 'prePersist', 'preFlush']);
    }

    /**
     * PostLoad
     */
    function it_transforms_values_of_an_entity_collection_field_into_entities_after_the_loading(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflBar,
        \ReflectionProperty $reflBarIds,
        BarStub $bar4,
        BarStub $bar8,
        BarStub $bar15,
        ReferencedCollectionFactory $factory,
        ReferencedCollection $collection
    ) {
        $args->getDocument()->willReturn($document);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = [
            'bar' => $reflBar,
            'barIds' => $reflBarIds,
        ];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme/Entity/Bar', 'idsField' => 'barIds'],
        ];

        $reflBar->getValue($document)->willReturn([]);
        $reflBarIds->getValue($document)->willReturn([4, 8, 15]);
        $factory->create('Acme/Entity/Bar', [4, 8, 15], $document)->willReturn($collection);

        $reflBar->setValue($document, $collection)->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_throws_exception_when_entity_collection_field_has_no_target_entity_after_the_loading(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "targetEntity" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPostLoad($args);
    }

    function it_throws_exception_when_entity_collection_field_has_no_ids_field_after_the_loading(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme\Entity\Bar'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "idsField" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPostLoad($args);
    }

    /**
     * PrePersist
     */
    function it_transforms_values_of_an_entity_collection_field_into_entities_before_persisting(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflBar,
        Collection $currentValues,
        ReferencedCollectionFactory $factory,
        ReferencedCollection $collection
    ) {
        $args->getDocument()->willReturn($document);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = [
            'bar' => $reflBar,
        ];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme/Entity/Bar', 'idsField' => 'barIds'],
        ];

        $reflBar->getValue($document)->willReturn($currentValues);
        $factory->createFromCollection('Acme/Entity/Bar', $document, $currentValues)->willReturn($collection);

        $reflBar->setValue($document, $collection)->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_throws_exception_when_entity_collection_field_has_no_target_entity_before_persisting(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "targetEntity" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPrePersist($args);
    }

    function it_throws_exception_when_entity_collection_field_has_no_ids_field_before_persisting(
        LifecycleEventArgs $args,
        ValuesStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entities', 'targetEntity' => 'Acme\Entity\Bar'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "idsField" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPrePersist($args);
    }

    function it_synchronizes_ids_field_with_entities_type_of_scheduled_to_update_documents_before_flushing(
        PreFlushEventArgs $args,
        DocumentManager $dm,
        UnitOfWork $uow,
        ClassMetadata $metadata,
        ValuesStub $document,
        \ReflectionProperty $reflFoo,
        \ReflectionProperty $reflFooIds,
        ReferencedCollection $bars
    ) {
        $args->getDocumentManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);
        $uow->getScheduledDocumentUpdates()->willReturn([$document]);
        $uow->getScheduledDocumentInsertions()->willReturn([]);

        $dm->getClassMetadata(Argument::any())->willReturn($metadata);
        $metadata->fieldMappings = [
            'foo' => ['type' => 'entities', 'idsField' => 'fooIds']
        ];
        $metadata->reflFields = [
            'foo' => $reflFoo,
            'fooIds' => $reflFooIds,
        ];
        $reflFoo->getValue($document)->willReturn($bars);
        $bars->map(Argument::any())->willReturn($bars);
        $bars->toArray()->willReturn([1,2,3]);

        $reflFooIds->setValue($document, [1,2,3])->shouldBeCalled();

        $this->preFlush($args);
    }
}

class ValuesStub
{
}

class BarStub
{
}
