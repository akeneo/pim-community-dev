<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GroupSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        SavingOptionsResolverInterface $optionsResolver,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productSaver,
            $versionContext,
            $optionsResolver,
            $eventDispatcher,
            $pqbFactory,
            $detacher,
            'Pim\Bundle\CatalogBundle\Model'
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_saves_a_group_and_flushes_by_default(
        $objectManager,
        $optionsResolver,
        $eventDispatcher,
        GroupInterface $group,
        GroupType $type
    ) {
        $optionsResolver->resolveSaveOptions([])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
            ]
        );

        $group->getProducts()->willReturn(new ArrayCollection([]));
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(null);

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $this->save($group);
    }

    function it_saves_a_group_and_added_products(
        $optionsResolver,
        $objectManager,
        $productSaver,
        $eventDispatcher,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $addedProduct
    ) {
        $optionsResolver->resolveSaveOptions(['add_products' => [$addedProduct]])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
            ]
        );

        $group->getProducts()->willReturn(new ArrayCollection([$addedProduct]));
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(null);

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver->saveAll([$addedProduct])->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($group, ['add_products' => [$addedProduct]]);
    }

    function it_saves_a_group_and_removed_products(
        $optionsResolver,
        $objectManager,
        $productSaver,
        $eventDispatcher,
        $pqbFactory,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $removedProduct,
        ProductQueryBuilderInterface $pqb
    ) {
        $optionsResolver->resolveSaveOptions(['remove_products' => [$removedProduct]])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => false,
            ]
        );

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('groups', 'IN', ['foo'])->shouldBeCalled();
        $pqb->execute()->willReturn([$removedProduct]);

        $group->getProducts()->willReturn(new ArrayCollection([]));
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(42);
        $group->getCode()->willReturn('foo');

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver->saveAll([$removedProduct])->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($group, ['remove_products' => [$removedProduct]]);
    }


    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
