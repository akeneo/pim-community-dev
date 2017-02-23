<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\EventListener\CatalogUpdatesSubscriber;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Remover\ChainedProjectRemover;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CatalogUpdatesSubscriberSpec extends ObjectBehavior
{
    function let(ChainedProjectRemover $chainedRemover)
    {
        $this->beConstructedWith($chainedRemover);
    }

    function it_is_catalog_updates_subscriber()
    {
        $this->shouldHaveType(CatalogUpdatesSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'removeProjectsImpactedByEntity',
            StorageEvents::POST_SAVE => 'removeProjectsImpactedByEntity',
        ]);
    }

    function it_removes_projects_on_entities_pre_remove(
        $chainedRemover,
        GenericEvent $event,
        ChannelInterface $channel
    ) {
        $event->getSubject()->willReturn($channel);
        $chainedRemover->removeProjectsImpactedBy($channel, StorageEvents::PRE_REMOVE)->shouldBeCalled();

        $this->removeProjectsImpactedByEntity($event, StorageEvents::PRE_REMOVE);
    }

    function it_removes_projects_on_entities_post_save(
        $chainedRemover,
        GenericEvent $event,
        LocaleInterface $locale
    ) {
        $event->getSubject()->willReturn($locale);
        $chainedRemover->removeProjectsImpactedBy($locale, StorageEvents::POST_SAVE)->shouldBeCalled();

        $this->removeProjectsImpactedByEntity($event, StorageEvents::POST_SAVE);
    }

    function it_does_not_remove_projects_from_on_project_events(
        $chainedRemover,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);

        $chainedRemover->removeProjectsImpactedBy($project, StorageEvents::POST_SAVE)->shouldNotBeCalled();;
        $chainedRemover->removeProjectsImpactedBy($project, StorageEvents::PRE_REMOVE)->shouldNotBeCalled();;

        $this->removeProjectsImpactedByEntity($event, StorageEvents::POST_SAVE);
        $this->removeProjectsImpactedByEntity($event, StorageEvents::PRE_REMOVE);
    }
}
