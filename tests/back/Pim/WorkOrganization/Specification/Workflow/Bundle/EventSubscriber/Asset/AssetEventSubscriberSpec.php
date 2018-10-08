<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Asset;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\PublishedProductConsistencyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AssetEventSubscriberSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($pqbFactory, $attributeRepository);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_returns_the_events_it_subscribed_to()
    {
        $this::getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'checkPublishedProductConsistency',
        ]);
    }

    function it_does_nothing_if_the_asset_is_not_used_in_a_published_product(
        $pqbFactory,
        $attributeRepository,
        GenericEvent $event,
        AssetInterface $asset,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $publishedProducts
    ) {
        $event->getSubject()->willReturn($asset);

        $asset->getCode()->willReturn('my_asset');

        $attributeRepository
            ->getAttributeCodesByType('pim_assets_collection')
            ->willReturn(['attribute_code_1', 'attribute_code_2']);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code_1', 'IN', ['my_asset'])->willReturn($pqb);
        $pqb->addFilter('attribute_code_2', 'IN', ['my_asset'])->willReturn($pqb);
        $pqb->execute()->willReturn($publishedProducts);

        $publishedProducts->count()->willReturn(0);

        $this->checkPublishedProductConsistency($event)->shouldReturn($event);
    }

    function it_throws_an_exception_if_the_asset_is_used_in_a_published_product(
        $pqbFactory,
        $attributeRepository,
        GenericEvent $event,
        AssetInterface $asset,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $publishedProducts
    ) {
        $event->getSubject()->willReturn($asset);

        $asset->getCode()->willReturn('my_asset');

        $attributeRepository
            ->getAttributeCodesByType('pim_assets_collection')
            ->willReturn(['attribute_code_1']);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code_1', 'IN', ['my_asset'])->willReturn($pqb);
        $pqb->execute()->willReturn($publishedProducts);

        $publishedProducts->count()->willReturn(2);

        $this
            ->shouldThrow(
                new PublishedProductConsistencyException(
                    'Impossible to remove an asset linked to a published product'
                )
            )
            ->during('checkPublishedProductConsistency', [$event]);
    }
}
