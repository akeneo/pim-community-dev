<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Prophecy\Argument;

class TimestampableSubscriberSpec extends ObjectBehavior
{
    function it_is_a_doctrine_event_listener()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_pre_persist_and_pre_update_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist', 'preUpdate']);
    }

    function it_does_not_apply_during_pre_persist_on_non_timestampable_object(LifecycleEventArgs $args, NonTimestampableInterface $object)
    {
        $args->getObject()->willReturn($object);
        $object->setCreated()->shouldNotBeCalled();
        $object->setUpdated()->shouldNotBeCalled();

        $this->prePersist($args);
    }

    function it_applies_during_pre_persist_on_timestampable_object(LifecycleEventArgs $args, TimestampableInterface $object)
    {
        $args->getObject()->willReturn($object);
        $object->setCreated(Argument::type('\DateTime'))->shouldBeCalled();
        $object->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_does_not_apply_during_pre_update_on_non_timestampable_object(LifecycleEventArgs $args, NonTimestampableInterface $object)
    {
        $args->getObject()->willReturn($object);
        $object->setCreated()->shouldNotBeCalled();
        $object->setUpdated()->shouldNotBeCalled();

        $this->preUpdate($args);
    }

    function it_does_not_apply_during_pre_update_on_versionable_object(LifecycleEventArgs $args, NonTimestampableVersionableInterface $object)
    {
        $args->getObject()->willReturn($object);
        $object->setCreated()->shouldNotBeCalled();
        $object->setUpdated()->shouldNotBeCalled();

        $this->preUpdate($args);
    }

    function it_applies_during_pre_update_on_timestampable_object(LifecycleEventArgs $args, TimestampableInterface $object)
    {
        $args->getObject()->willReturn($object);
        $object->setCreated()->shouldNotBeCalled();
        $object->setUpdated(Argument::type('\DateTime'))->shouldBeCalled();

        $this->preUpdate($args);
    }
}

interface NonTimestampableInterface
{
    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);
}

interface NonTimestampableVersionableInterface extends VersionableInterface
{
    /**
     * @param \DateTime $updated
     */
    public function setUpdated(\DateTime $updated);

    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created);
}
