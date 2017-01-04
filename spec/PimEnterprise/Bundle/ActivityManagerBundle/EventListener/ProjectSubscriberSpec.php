<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Job\ProjectCalculationJobLauncher;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProjectSubscriberSpec extends ObjectBehavior
{
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
}
