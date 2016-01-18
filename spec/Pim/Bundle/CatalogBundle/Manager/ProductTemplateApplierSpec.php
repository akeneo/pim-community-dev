<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTemplateApplierSpec extends ObjectBehavior
{
    function let(
        ProductTemplateUpdaterInterface $templateUpdater,
        ValidatorInterface $productValidator,
        ObjectDetacherInterface $productDetacher,
        BulkSaverInterface $productSaver
    ) {
        $this->beConstructedWith(
            $templateUpdater,
            $productValidator,
            $productDetacher,
            $productSaver
        );
    }

    function it_applies_template_values_on_products(
        ProductTemplateInterface $template,
        ProductInterface $productOne,
        ProductInterface $productTwo,
        $templateUpdater,
        $productValidator,
        $productSaver,
        ConstraintViolationList $emptyViolationList
    ) {
        $templateUpdater->update($template, [$productOne, $productTwo])->shouldBeCalled();

        $productValidator->validate($productOne)
            ->shouldBeCalled()
            ->willReturn($emptyViolationList);
        $productValidator->validate($productTwo)
            ->shouldBeCalled()
            ->willReturn($emptyViolationList);
        $emptyViolationList->count()->willReturn(0);

        $productSaver->saveAll([$productOne, $productTwo])->shouldBeCalled();

        $this->apply($template, [$productOne, $productTwo]);
    }

    function it_skip_product_if_invalid_after_template_values_appliance(
        ProductTemplateInterface $template,
        ProductInterface $validProduct,
        ProductInterface $invalidProduct,
        $templateUpdater,
        $productValidator,
        $productDetacher,
        $productSaver,
        ConstraintViolationList $emptyViolationList,
        ConstraintViolationList $notEmptyViolationList
    ) {
        $templateUpdater->update($template, [$validProduct, $invalidProduct])->shouldBeCalled();

        $productValidator->validate($validProduct)
            ->shouldBeCalled()
            ->willReturn($emptyViolationList);
        $productValidator->validate($invalidProduct)
            ->shouldBeCalled()
            ->willReturn($notEmptyViolationList);
        $emptyViolationList->count()->willReturn(0);
        $notEmptyViolationList->count()->willReturn(1);
        $notEmptyViolationList->getIterator()->willReturn(new ArrayCollection());

        $productDetacher->detach($invalidProduct)->shouldBeCalled();
        $productSaver->saveAll([$validProduct])->shouldBeCalled();

        $this->apply($template, [$validProduct, $invalidProduct]);
    }
}
