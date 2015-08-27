<?php

namespace spec\PimEnterprise\Component\ProductAsset\Remover;

use Akeneo\Bundle\StorageUtilsBundle\Event\BaseEvents;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $class = 'PimEnterprise\Component\ProductAsset\Model\AssetInterface';
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher, $class);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_dispatches_an_event_when_removing_an_asset(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AssetInterface $asset
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);
        $eventDispatcher->dispatch(
            BaseEvents::PRE_REMOVE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            AssetEvent::PRE_REMOVE,
            Argument::type('PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent')
        )->shouldBeCalled();

        $objectManager->remove($asset)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            BaseEvents::POST_REMOVE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            AssetEvent::POST_REMOVE,
            Argument::type('PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent')
        )->shouldBeCalled();

        $this->remove($asset);
    }
}
