<?php

namespace spec\PimEnterprise\Component\ProductAsset\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\CategoryAssetEvents;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CategoryAssetRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        BulkSaverInterface $assetSaver
    ) {
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher, $assetSaver);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_dispatches_an_event_when_removing_a_category(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        $assetSaver,
        CategoryInterface $category,
        AssetInterface $asset1,
        AssetInterface $asset2
    ) {
        $optionsResolver->resolveRemoveOptions(['flush' => true])->willReturn(['flush' => true]);
        $category->getId()->willReturn(12);
        $category->isRoot()->willReturn(false);
        $category->getAssets()->willReturn([$asset1, $asset2]);
        $asset1->removeCategory($category)->shouldBeCalled();
        $asset2->removeCategory($category)->shouldBeCalled();

        $eventDispatcher->dispatch(
            CategoryAssetEvents::PRE_REMOVE_CATEGORY,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $objectManager->remove($category)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            CategoryAssetEvents::POST_REMOVE_CATEGORY,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $assetSaver->saveAll(
            [$asset1, $asset2],
            [
                'flush' => true,
            ]
        )->shouldBeCalled();

        $this->remove($category, ['flush' => true]);
    }

    function it_dispatches_an_event_when_removing_a_tree(
        $eventDispatcher,
        $objectManager,
        CategoryInterface $tree
    ) {
        $tree->getId()->willReturn(12);
        $tree->isRoot()->willReturn(true);
        $tree->getAssets()->willReturn([]);

        $eventDispatcher->dispatch(
            CategoryAssetEvents::PRE_REMOVE_TREE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $objectManager->remove($tree)->shouldBeCalled();

        $eventDispatcher->dispatch(
            CategoryAssetEvents::POST_REMOVE_TREE,
            Argument::type('Akeneo\Component\StorageUtils\Event\RemoveEvent')
        )->shouldBeCalled();

        $this->remove($tree, ['flush' => true]);
    }

    function it_throws_exception_when_remove_anything_else_than_a_category()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "PimEnterprise\Component\ProductAsset\Model\CategoryInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
