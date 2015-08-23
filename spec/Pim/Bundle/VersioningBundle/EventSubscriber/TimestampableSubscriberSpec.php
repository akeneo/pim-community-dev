<?php

namespace spec\Pim\Bundle\VersioningBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as ODMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork as ORMUnitOfWork;
use Doctrine\ODM\MongoDB\UnitOfWork as ODMUnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\TimestampableInterface;
use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\Mapping\ClassMetadata
 * @require Doctrine\ODM\MongoDB\UnitOfWork
 */
class TimestampableSubscriberSpec extends ObjectBehavior
{
    function let(ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_a_doctrine_event_listener()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_pre_persist_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist']);
    }

    function it_does_not_apply_on_non_version_object(LifecycleEventArgs $args, \stdClass $object)
    {
        $args->getObject()->willReturn($object);
        $args->getObjectManager()->shouldNotBeCalled();

        $this->prePersist($args);
    }

    function it_does_not_apply_on_non_timestampable_versioned_object(
        $registry,
        LifecycleEventArgs $args,
        ObjectManager $om,
        Version $version,
        ORMClassMetadata $metadata
    ) {
        $registry->getManagerForClass('bar')->willReturn($om);
        $om->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass('spec\Pim\Bundle\VersioningBundle\EventSubscriber\NonTimestampableInterface'));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');

        $args->getObject()->willReturn($version);

        $om->find()->shouldNotBeCalled();

        $this->prePersist($args);
    }

    function it_applies_on_timestampable_versioned_object_with_an_entity_manager(
        $registry,
        LifecycleEventArgs $args,
        EntityManager $om,
        ORMUnitOfWork $uow,
        Version $version,
        TimestampableInterface $object,
        ORMClassMetadata $metadata
    ) {
        $registry->getManagerForClass('bar')->willReturn($om);
        $om->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass('Pim\Bundle\CatalogBundle\Model\TimestampableInterface'));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');
        $version->getLoggedAt()->willReturn('foobar');

        $args->getObject()->willReturn($version);

        $om->getUnitOfWork()->willReturn($uow);
        $om->find('bar', 'foo')->willReturn($object);

        $uow->computeChangeSet($metadata, $object)->shouldBeCalled();

        $object->setUpdated('foobar')->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_applies_on_timestampable_versioned_object_with_a_document_manager(
        $registry,
        LifecycleEventArgs $args,
        DocumentManager $om,
        ODMUnitOfWork $uow,
        Version $version,
        TimestampableInterface $object,
        ODMClassMetadata $metadata
    ) {
        $registry->getManagerForClass('bar')->willReturn($om);
        $om->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass('Pim\Bundle\CatalogBundle\Model\TimestampableInterface'));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');
        $version->getLoggedAt()->willReturn('foobar');

        $args->getObject()->willReturn($version);

        $om->getUnitOfWork()->willReturn($uow);
        $om->find('bar', 'foo')->willReturn($object);

        $uow->computeChangeSet($metadata, $object)->shouldBeCalled();

        $object->setUpdated('foobar')->shouldBeCalled();

        $this->prePersist($args);
    }
}

interface NonTimestampableInterface
{
    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);
}
