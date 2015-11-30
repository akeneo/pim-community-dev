<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_the_events_it_subscribed_to()
    {
        $this::getSubscribedEvents()->shouldReturn([
            AssetEvent::PRE_REMOVE => 'checkPublishedProductConsistency',
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

        $asset->getId()->willReturn(42);

        $attributeRepository
            ->getAttributeCodesByType('pim_assets_collection')
            ->willReturn(['attribute_code_1', 'attribute_code_2']);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code_1.id', 'IN', [42])->willReturn($pqb);
        $pqb->addFilter('attribute_code_2.id', 'IN', [42])->willReturn($pqb);
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

        $asset->getId()->willReturn(42);

        $attributeRepository
            ->getAttributeCodesByType('pim_assets_collection')
            ->willReturn(['attribute_code_1']);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('attribute_code_1.id', 'IN', [42])->willReturn($pqb);
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
