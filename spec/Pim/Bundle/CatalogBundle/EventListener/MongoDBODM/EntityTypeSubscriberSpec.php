<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;

/**
 * @require Doctrine\ODM\MongoDB\Events
 * @require Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
 * @require Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\Mapping\ClassMetadata
 */
class EntityTypeSubscriberSpec extends ObjectBehavior
{
    function let(EntityManager $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_an_event_subsriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad', 'preUpdate']);
    }

    function it_transforms_value_of_a_entity_field_into_lazy_reference_to_an_entity (
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflFoo,
        FooStub $foo16,
        EntityManager $entityManager
    ) {
        $args->getDocument()->willReturn($entity);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = ['bar' => $reflFoo];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity', 'targetEntity' => 'Acme/Entity/Foo'],
        ];

        $reflFoo->getValue($entity)->willReturn(16);
        $entityManager->getReference('Acme/Entity/Foo', 16)->willReturn($foo16);

        $reflFoo->setValue($entity, $foo16)->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_convert_value_if_initial_value_is_null(
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflFoo
    ) {
        $args->getDocument()->willReturn($entity);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = ['bar' => $reflFoo];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity', 'targetEntity' => 'Acme/Entity/Foo'],
        ];

        $reflFoo->getValue($entity)->willReturn(null);

        $reflFoo->setValue(Argument::cetera())->shouldNotBeCalled();

        $this->postLoad($args);
    }

    function it_does_not_convert_already_converted_value(
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflFoo,
        FooStub $foo
    ) {
        $args->getDocument()->willReturn($entity);
        $args->getDocumentManager()->willReturn($dm);

        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->reflFields = ['bar' => $reflFoo];
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => [
                'type'         => 'entity',
                'targetEntity' => 'spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM\FooStub'
            ],
        ];

        $reflFoo->getValue($entity)->willReturn($foo);

        $reflFoo->setValue(Argument::cetera())->shouldNotBeCalled();

        $this->postLoad($args);
    }

    function it_throws_exception_when_entity_collection_field_has_no_target_entity(
        LifecycleEventArgs $args,
        ValueStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getDocument()->willReturn($document);
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity'],
        ];
        $documentMetadata->name = 'Acme/Document';

        $this
            ->shouldThrow(
                new \RuntimeException('Please provide the "targetEntity" of the Acme/Document::$bar field mapping')
            )
            ->duringPostLoad($args);
    }

    function it_transformes_entities_into_ids_before_update(
        PreUpdateEventArgs $args,
        ValueStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        FooStub $entity
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity'],
        ];
        $args->hasChangedField('bar')->willReturn(true);
        $args->getNewValue('bar')->willReturn($entity);
        $entity->getId()->willReturn('45');

        $args->setNewValue('bar', 45)->shouldBeCalled();

        $this->preUpdate($args);
    }

    function it_keeps_old_value_if_new_entity_has_no_id_before_update(
        PreUpdateEventArgs $args,
        ValueStub $document,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        FooStub $entity
    ) {
        $args->getDocument()->willReturn($document->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity'],
        ];
        $args->hasChangedField('bar')->willReturn(true);
        $args->getNewValue('bar')->willReturn($entity);
        $args->getOldValue('bar')->willReturn(26);
        $entity->getId()->willReturn(null);

        $args->setNewValue('bar', 26)->shouldBeCalled();

        $this->preUpdate($args);
    }
}

class ValueStub
{
}

class FooStub
{
    public function getId() { }
}
