<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;

class ResolveTargetRepositorySubscriberSpec extends ObjectBehavior
{
    function it_is_a_doctrine_subscriber()
    {
        $this->shouldHaveType('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_the_load_class_metadata_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['loadClassMetadata']);
    }

    function it_adds_new_targeted_repository(LoadClassMetadataEventArgs $args, ClassMetadata $cm)
    {
        $this->addResolveTargetRepository('foo', 'barRepository');

        $args->getClassMetadata()->willReturn($cm);
        $cm->getName()->willReturn('foo');

        $this->loadClassMetadata($args);
    }
}
