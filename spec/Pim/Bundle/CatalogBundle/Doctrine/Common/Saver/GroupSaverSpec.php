<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

class GroupSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateMediaManager $templateMediaManager,
        ProductTemplateApplierInterface $templateApplier,
        VersionManager $versionManager
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productSaver,
            $templateMediaManager,
            $templateApplier,
            $versionManager,
            'Pim\Bundle\CatalogBundle\Model'
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_saves_a_group_and_flushes_by_default($objectManager, GroupInterface $group, GroupType $type)
    {
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($group);
    }

    function it_saves_a_group_and_added_products(
        $objectManager,
        $productSaver,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $addedProduct
    ) {
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$addedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['add_products' => [$addedProduct]]);
    }

    function it_saves_a_group_and_removed_products(
        $objectManager,
        $productSaver,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $removedProduct
    ) {
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$removedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['remove_products' => [$removedProduct]]);
    }

    function it_handles_media_values_of_variant_group_product_templates(
        $templateMediaManager,
        GroupInterface $group,
        GroupType $type,
        ProductTemplateInterface $template
    ) {
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $type->isVariant()->willReturn(true);
        $group->getProductTemplate()->willReturn($template);

        $templateMediaManager->handleProductTemplateMedia($template)->shouldBeCalled();

        $this->save($group);
    }

    function it_saves_a_variant_group_and_copies_values_to_products(
        $objectManager,
        $templateApplier,
        GroupInterface $group,
        GroupType $type,
        ProductInterface $product,
        ProductTemplateInterface $template,
        ArrayCollection $products
    ) {
        $group->getType()->willReturn($type);
        $group->getCode()->willReturn('my_code');
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $type->isVariant()->willReturn(true);
        $group->getProductTemplate()->willReturn($template);
        $group->getProducts()->willReturn($products);
        $products->toArray()->willReturn([$product]);

        $templateApplier
            ->apply($template, [$product])
            ->shouldBeCalled();

        $this->save($group, ['copy_values_to_products' => true]);
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
