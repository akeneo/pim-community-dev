<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectRemoverSubscriber;
use PimEnterprise\Component\ActivityManager\Remover\ProjectRemoverEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProjectRemoverSubscriberSpec extends ObjectBehavior
{
    function let(ProjectRemoverEngine $projectRemoverEngine)
    {
        $this->beConstructedWith($projectRemoverEngine);
    }

    function it_is_project_remover_subscriber()
    {
        $this->shouldHaveType(ProjectRemoverSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'removeProjects',
        ]);
    }

    function it_removes_projects($projectRemoverEngine, GenericEvent $event, ChannelInterface $channel)
    {
        $event->getSubject()->willReturn($channel);
        $projectRemoverEngine->remove($channel)->shouldBeCalled();

        $this->removeProjects($event);
    }
}
