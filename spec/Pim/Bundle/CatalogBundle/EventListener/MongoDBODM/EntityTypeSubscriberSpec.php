<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

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

    /**
     * @require extension mongo
     * @require class Doctrine\ODM\MongoDB\Events
     */
    function it_subscribes_to_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::postLoad]);
    }

    /**
     * @require extension mongo
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     * @require class Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    function it_transforms_value_of_a_entity_field_into_lazy_reference_to_an_entity (
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflFoo,
        FooStub $foo16,
        EntityManager $entityManager
    ) {
        $args->getEntity()->willReturn($entity);
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

    /**
     * @require extension mongo
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     * @require class Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    function it_does_not_convert_value_if_initial_value_is_null(
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata,
        \ReflectionProperty $reflFoo
    ) {
        $args->getEntity()->willReturn($entity);
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

    /**
     * @require extension mongo
     * @require class Doctrine\ODM\MongoDB\Event\LifecycleEventArgs
     * @require class Doctrine\ODM\MongoDB\DocumentManager
     * @require class Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    function it_throws_exception_when_entity_collection_field_has_no_target_entity(
        LifecycleEventArgs $args,
        ValueStub $entity,
        DocumentManager $dm,
        ClassMetadata $documentMetadata
    ) {
        $args->getEntity()->willReturn($entity->getWrappedObject());
        $args->getDocumentManager()->willReturn($dm);
        $dm->getClassMetadata(Argument::any())->willReturn($documentMetadata);
        $documentMetadata->fieldMappings = [
            'foo' => ['type' => 'text'],
            'bar' => ['type' => 'entity'],
        ];
        $documentMetadata->name = 'Acme/Entity';

        $exception = new \RuntimeException('Please provide the "targetEntity" of the Acme/Entity::$bar field mapping');
        $this->shouldThrow($exception)->duringPostLoad($args);
    }
}

class ValueStub
{
}

class FooStub
{
}
