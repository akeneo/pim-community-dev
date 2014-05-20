<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;

use PhpSpec\ObjectBehavior;

class ResolveTargetRepositorySubscriberSpec extends ObjectBehavior
{
    public function it_is_a_doctrine_subscriber()
    {
        $this->shouldHaveType('Doctrine\Common\EventSubscriber');
    }

    public function it_subscribes_to_the_loadClassMetadata_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['loadClassMetadata']);
    }

    public function it_adds_new_targeted_repository(LoadClassMetadataEventArgs $args, ClassMetadata $cm)
    {
        $this->addResolveTargetRepository('foo', 'barRepository');

        $args->getClassMetadata()->willReturn($cm);
        $cm->getName()->willReturn('foo');

        $this->loadClassMetadata($args);
    }
}
