<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork as ORMUnitOfWork;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;

class TimestampableSubscriberSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em)
    {
        $this->beConstructedWith($em);
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
        $em,
        LifecycleEventArgs $args,
        Version $version,
        ORMClassMetadata $metadata
    ) {
        $em->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass(NonTimestampableInterface::class));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');

        $args->getObject()->willReturn($version);

        $em->find()->shouldNotBeCalled();

        $this->prePersist($args);
    }

    function it_applies_on_timestampable_versioned_object_with_an_entity_manager(
        $em,
        LifecycleEventArgs $args,
        ORMUnitOfWork $uow,
        Version $version,
        TimestampableInterface $object,
        ORMClassMetadata $metadata
    ) {
        $em->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass(TimestampableInterface::class));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');
        $version->getLoggedAt()->willReturn('foobar');

        $args->getObject()->willReturn($version);

        $em->getUnitOfWork()->willReturn($uow);
        $em->find('bar', 'foo')->willReturn($object);

        $uow->computeChangeSet($metadata, $object)->shouldBeCalled();

        $object->setUpdated('foobar')->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_applies_on_timestampable_versioned_object_with_a_document_manager(
        $em,
        LifecycleEventArgs $args,
        UnitOfWork $uow,
        Version $version,
        TimestampableInterface $object,
        ClassMetadata $metadata
    ) {
        $em->getClassMetadata('bar')->willReturn($metadata);
        $metadata->getReflectionClass()->willReturn(new \ReflectionClass(TimestampableInterface::class));

        $version->getResourceId()->willReturn('foo');
        $version->getResourceName()->willReturn('bar');
        $version->getLoggedAt()->willReturn('foobar');

        $args->getObject()->willReturn($version);

        $em->getUnitOfWork()->willReturn($uow);
        $em->find('bar', 'foo')->willReturn($object);

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
