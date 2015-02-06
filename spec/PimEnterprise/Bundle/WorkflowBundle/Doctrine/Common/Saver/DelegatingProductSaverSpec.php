<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver\ProductDraftSaver;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        ProductSaver $workingCopySaver,
        ProductDraftSaver $draftSaver,
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        SecurityContextInterface $securityContext
    ) {
        $this->beConstructedWith(
            $workingCopySaver,
            $draftSaver,
            $objectManager,
            $optionsResolver,
            $securityContext
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_delegates_to_working_copy_saver_when_user_is_the_owner(
        ProductInterface $product,
        $optionsResolver,
        $securityContext,
        $workingCopySaver
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(42);
        $securityContext->isGranted(Attributes::OWN, $product)
            ->shouldBeCalled()
            ->willReturn(true);

        $workingCopySaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_working_copy_saver_when_user_is_not_the_owner_and_product_not_exists(
        ProductInterface $product,
        $optionsResolver,
        $workingCopySaver
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(null);

        $workingCopySaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_draft_saver_when_user_is_not_the_owner_and_product_exists(
        ProductInterface $product,
        $optionsResolver,
        $securityContext,
        $draftSaver
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(42);
        $securityContext->isGranted(Attributes::OWN, $product)
            ->shouldBeCalled()
            ->willReturn(false);

        $draftSaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_throws_an_exception_when_try_to_save_something_else_than_a_product(
        $objectManager
    ) {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}