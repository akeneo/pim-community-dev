<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectRemoverSubscriber;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
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
            StorageEvents::POST_SAVE => 'removeProjectsFromDeactivatedLocale',
        ]);
    }

    function it_removes_projects_on_entities_pre_remove(
        $projectRemoverEngine,
        GenericEvent $event,
        ChannelInterface $channel
    ) {
        $event->getSubject()->willReturn($channel);
        $projectRemoverEngine->remove($channel)->shouldBeCalled();

        $this->removeProjects($event);
    }

    function it_does_not_remove_projects_on_project_pre_remove(
        $projectRemoverEngine,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);
        $projectRemoverEngine->remove($project)->shouldNotBeCalled();

        $this->removeProjects($event);
    }

    function it_does_not_check_projects_on_pre_remove(
        $projectRemoverEngine,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);
        $projectRemoverEngine->remove($project)->shouldNotBeCalled();

        $this->removeProjects($event);
    }

    function it_removes_projects_from_locale(
        $projectRemoverEngine,
        GenericEvent $event,
        LocaleInterface $locale
    ) {
        $event->getSubject()->willReturn($locale);

        $projectRemoverEngine->remove($locale)->shouldBeCalled();

        $this->removeProjectsFromDeactivatedLocale($event);
    }

    function it_does_not_remove_projects_from_on_post_save_another_entity_than_locale(
        $projectRemoverEngine,
        GenericEvent $event,
        ChannelInterface $channel
    ) {
        $event->getSubject()->willReturn($channel);

        $projectRemoverEngine->remove($channel)->shouldNotBeCalled();;

        $this->removeProjectsFromDeactivatedLocale($event);
    }
}
