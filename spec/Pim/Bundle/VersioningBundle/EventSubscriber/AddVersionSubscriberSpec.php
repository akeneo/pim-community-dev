<?php

namespace spec\Pim\Bundle\VersioningBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddVersionSubscriberSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_a_doctrine_event_listener()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_on_and_post_flush_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['onFlush', 'postFlush']);
    }
}
