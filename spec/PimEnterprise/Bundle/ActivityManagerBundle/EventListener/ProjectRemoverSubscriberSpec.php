<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectRemoverSubscriber;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Remover\ChainedProjectRemover;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProjectRemoverSubscriberSpec extends ObjectBehavior
{
    function let(ChainedProjectRemover $chainedRemover)
    {
        $this->beConstructedWith($chainedRemover);
    }

    function it_is_project_remover_subscriber()
    {
        $this->shouldHaveType(ProjectRemoverSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'removeProjectsImpactedByEntity',
            StorageEvents::POST_SAVE => 'removeProjectsImpactedByLocale',
        ]);
    }

    function it_removes_projects_on_entities_pre_remove(
        $chainedRemover,
        GenericEvent $event,
        ChannelInterface $channel
    ) {
        $event->getSubject()->willReturn($channel);
        $chainedRemover->removeProjectsImpactedBy($channel)->shouldBeCalled();

        $this->removeProjectsImpactedByEntity($event);
    }

    function it_does_not_remove_projects_on_project_pre_remove(
        $chainedRemover,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);
        $chainedRemover->removeProjectsImpactedBy($project)->shouldNotBeCalled();

        $this->removeProjectsImpactedByEntity($event);
    }

    function it_does_not_check_projects_on_pre_remove(
        $chainedRemover,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);
        $chainedRemover->removeProjectsImpactedBy($project)->shouldNotBeCalled();

        $this->removeProjectsImpactedByEntity($event);
    }

    function it_removes_projects_from_locale(
        $chainedRemover,
        GenericEvent $event,
        LocaleInterface $locale
    ) {
        $event->getSubject()->willReturn($locale);

        $chainedRemover->removeProjectsImpactedBy($locale)->shouldBeCalled();

        $this->removeProjectsImpactedByLocale($event);
    }

    function it_does_not_remove_projects_from_on_post_save_another_entity_than_locale(
        $chainedRemover,
        GenericEvent $event,
        ChannelInterface $channel
    ) {
        $event->getSubject()->willReturn($channel);

        $chainedRemover->removeProjectsImpactedBy($channel)->shouldNotBeCalled();;

        $this->removeProjectsImpactedByLocale($event);
    }
}
