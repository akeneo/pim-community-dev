<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductCascadeRemovalRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AssetEventSubscriberSpec extends ObjectBehavior
{
    function let(
        ProductCascadeRemovalRepositoryInterface $cascadeRemovalRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($cascadeRemovalRepository, $attributeRepository);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_the_events_it_subscribed_to()
    {
        $this::getSubscribedEvents()->shouldReturn([
            AssetEvent::POST_REMOVE => 'cascadeAssetRemove',
        ]);
    }

    function it_updates_product_document_when_an_asset_related_to_a_product_is_removed(
        $attributeRepository,
        $cascadeRemovalRepository,
        GenericEvent $event,
        AssetInterface $asset
    ) {
        $event->getSubject()->willReturn($asset);
        $attributeRepository
            ->getAttributeCodesByType('pim_assets_collection')
            ->willReturn(['attribute_code_1', 'attribute_code_2']);
        $cascadeRemovalRepository
            ->cascadeAssetRemoval($asset, ['attribute_code_1', 'attribute_code_2'])
            ->shouldBeCalled();

        $this->cascadeAssetRemove($event)->shouldReturn($event);
    }
}
