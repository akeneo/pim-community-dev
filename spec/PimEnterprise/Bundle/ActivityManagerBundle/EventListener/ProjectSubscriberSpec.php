<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectSubscriber;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProjectSubscriberSpec extends ObjectBehavior
{
    function let(PreProcessingRepositoryInterface $preProcessingRepository)
    {
        $this->beConstructedWith($preProcessingRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'generateCode',
            StorageEvents::PRE_REMOVE => 'removePreProcessedEntries',
        ]);
    }

    function it_generates_project_code(
        GenericEvent $event,
        ProjectInterface $project,
        LocaleInterface $locale,
        ChannelInterface $channel,
        DatagridView $datagridView
    ) {
        $event->getSubject()->willReturn($project);
        $project->getDatagridView()->willReturn($datagridView);

        $project->getLabel()->willreturn('My project');
        $project->getLocale()->willreturn($locale);
        $project->getChannel()->willreturn($channel);

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('fr_FR');

        $project->setCode('my-project-ecommerce-fr-fr')->shouldBeCalled();
        $datagridView->setLabel('my-project-ecommerce-fr-fr')->shouldBeCalled();

        $this->generateCode($event)->shouldReturn(null);
    }
    
    function it_removes_pre_processed_entries_when_a_project_is_deleted(
        $preProcessingRepository,
        GenericEvent $event,
        ProjectInterface $project
    ) {
        $event->getSubject()->willReturn($project);
        $preProcessingRepository->remove($project)->shouldBeCalled();

        $this->removePreProcessedEntries($event);
    }

    function it_does_nothing_if_subject_event_is_not_a_project(
        $preProcessingRepository,
        $object,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($object);
        $preProcessingRepository->remove($object)->shouldNotBeCalled();

        $this->removePreProcessedEntries($event);
    }
}
