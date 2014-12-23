<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateManager;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;

class GroupSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, BulkSaverInterface $productSaver, ProductTemplateApplierInterface $templateApplier)
    {
        $this->beConstructedWith($objectManager, $productSaver, $templateApplier);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_saves_a_group_and_flush_by_default($objectManager, GroupInterface $group, GroupType $type)
    {
        $group->getType()->willReturn($type);
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($group);
    }

    function it_saves_a_group_and_added_products($objectManager, GroupInterface $group, GroupType $type, ProductInterface $addedProduct, $productSaver)
    {
        $group->getType()->willReturn($type);
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$addedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['add_products' => [$addedProduct]]);
    }

    function it_saves_a_group_and_removed_products($objectManager, GroupInterface $group, GroupType $type, ProductInterface $removedProduct, $productSaver)
    {
        $group->getType()->willReturn($type);
        $objectManager->persist($group)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $productSaver
            ->saveAll([$removedProduct], ['recalculate' => false, 'schedule' => false])
            ->shouldBeCalled();

        $this->save($group, ['remove_products' => [$removedProduct]]);
    }

    function it_saves_a_variant_group_and_copy_values_to_products($objectManager, GroupInterface $group, GroupType $type, ProductInterface $product, $templateApplier, ProductTemplateInterface $template, ArrayCollection $products)
    {
        $group->getType()->willReturn($type);
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
