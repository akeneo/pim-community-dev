<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener\CatalogUpdatesSubscriber;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover\ChainedProjectRemover;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CatalogUpdatesSubscriberSpec extends ObjectBehavior
{
    function let(
        ChainedProjectRemover $chainedRemover,
        RefreshProjectCompletenessJobLauncher $attributeGroupCompletenessJobLauncher,
        RequestStack $requestStack
    ) {
        $this->beConstructedWith($chainedRemover, $attributeGroupCompletenessJobLauncher, $requestStack);
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
            StorageEvents::POST_SAVE => [
                ['removeProjectsImpactedByEntity', 100],
                ['updatePreProcessedData', 50]
            ],
        ]);
    }

    function it_updates_the_pre_processed_data_when_we_update_the_product_from_the_ui(
        $attributeGroupCompletenessJobLauncher,
        $requestStack,
        GenericEvent $event,
        ProductInterface $product,
        Request $request,
        ParameterBag $parameterBag,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $parameterBag->get('_route')->willReturn('pim_enrich_product_rest_post');
        $requestStack->getMasterRequest()->willReturn($request);
        $request->attributes = $parameterBag;

        $event->getSubject()->willReturn($product);
        $product->getLocale()->willReturn($locale);
        $product->getScope()->willReturn($channel);

        $attributeGroupCompletenessJobLauncher->launch($product, $channel, $locale);

        $this->updatePreProcessedData($event);
    }

    function it_only_pre_processes_attribute_group_completeness_for_product_entities(
        $attributeGroupCompletenessJobLauncher,
        $requestStack,
        GenericEvent $event,
        ProjectInterface $project,
        Request $request,
        ParameterBag $parameterBag
    ) {
        $parameterBag->get('_route')->willReturn('pim_enrich_product_rest_post');
        $requestStack->getMasterRequest()->willReturn($request);
        $request->attributes = $parameterBag;

        $event->getSubject()->willReturn($project);

        $attributeGroupCompletenessJobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updatePreProcessedData($event);
    }

    function it_only_pre_processes_attribute_group_completeness_for_product_edition(
        $attributeGroupCompletenessJobLauncher,
        GenericEvent $event,
        ProjectInterface $project,
        RequestStack $requestStack,
        Request $request,
        ParameterBag $parameterBag
    ) {
        $parameterBag->get('_route')->willReturn('route');
        $requestStack->getMasterRequest()->willReturn($request);
        $request->attributes = $parameterBag;

        $event->getSubject()->willReturn($project);

        $attributeGroupCompletenessJobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updatePreProcessedData($event);
    }

    function it_does_not_pre_processes_attribute_group_completeness_during_an_import(
        $requestStack,
        $attributeGroupCompletenessJobLauncher,
        GenericEvent $event
    ) {
        $requestStack->getMasterRequest()->willReturn(null);

        $attributeGroupCompletenessJobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->updatePreProcessedData($event);
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
