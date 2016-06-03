<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Component\Catalog\Manager\ProductTemplateApplierInterface;
use Pim\Component\Catalog\Manager\ProductTemplateMediaManager;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GroupSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateMediaManager $templateMediaManager,
        ProductTemplateApplierInterface $templateApplier,
        SavingOptionsResolverInterface $optionsResolver,
        VersionContext $versionContext,
        EventDispatcherInterface $eventDispatcher,
        ProductQueryBuilderFactoryInterface $pqbFactory
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productSaver,
            $templateMediaManager,
            $templateApplier,
            $versionContext,
            $optionsResolver,
            $eventDispatcher,
            $pqbFactory,
            'Pim\Bundle\CatalogBundle\Model'
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
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
        $pqb->addFilter('groups.id', 'IN', [42])->shouldBeCalled();
        $pqb->execute()->willReturn([$removedProduct]);

        $group->getProducts()->willReturn(new ArrayCollection([]));
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(42);

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver->saveAll([$removedProduct])->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($group, ['remove_products' => [$removedProduct]]);
    }

    function it_handles_media_values_of_variant_group_product_templates(
        $templateMediaManager,
        $eventDispatcher,
        GroupInterface $group,
        GroupType $type,
        ProductTemplateInterface $template
    ) {
        $group->getProducts()->willReturn(new ArrayCollection([]));
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $group->getId()->willReturn(null);
        $group->getProductTemplate()->willReturn($template);

        $type->isVariant()->willReturn(true);

        $templateMediaManager->handleProductTemplateMedia($template)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($group);
    }

    function it_saves_a_variant_group_and_copies_values_to_products(
        $optionsResolver,
        $objectManager,
        $templateApplier,
        $eventDispatcher,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $product,
        ProductTemplateInterface $template
    ) {
        $optionsResolver->resolveSaveOptions(['copy_values_to_products' => true])->willReturn(
            [
                'flush'                   => true,
                'copy_values_to_products' => true,
            ]
        );

        $group->getId()->willReturn(null);
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');

        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $type->isVariant()->willReturn(true);
        $group->getProductTemplate()->willReturn($template);
        $group->getProducts()->willReturn(new ArrayCollection([$product]));

        $templateApplier
            ->apply($template, [$product])
            ->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($group, ['copy_values_to_products' => true]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Component\Catalog\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
