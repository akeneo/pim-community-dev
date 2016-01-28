<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductDraftManagerSpec extends ObjectBehavior
{
    function let(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        ProductDraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith(
            $workingCopySaver,
            $userContext,
            $factory,
            $repository,
            $applier,
            $dispatcher,
            $saver,
            $remover
        );
    }

    function it_applies_changes_to_the_product_when_approving_a_product_draft(
        $workingCopySaver,
        $applier,
        $dispatcher,
        $remover,
        ProductDraftInterface $productDraft,
        ProductInterface $product
    ) {
        $productDraft->getChanges()->willReturn([
            'values' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => 'ecommerce', 'locale' => 'en_US', 'status' => ProductDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ]);
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getId()->willReturn(42);
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $applier->applyToReviewChanges($product, $productDraft)->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(false);
        $productDraft->removeChange('name', 'en_US', 'ecommerce')->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();
        $remover->remove($productDraft, ['flush' => false])->shouldBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->approve($productDraft);
    }

    function it_applies_changes_to_the_product_when_partially_approve_a_product_draft(
        $workingCopySaver,
        $factory,
        $applier,
        $dispatcher,
        $saver,
        $remover,
        ProductDraftInterface $productDraft,
        ProductDraftInterface $partialDraft,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('Mary');
        $productDraft->getChange('name', null, null)->willReturn('new name');
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $attribute->getLabel()->willReturn('Name');
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $changes = [
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'new name']
                ]
            ]
        ];
        $partialDraft->setChanges($changes)->shouldBeCalled();
        $partialDraft->getChanges()->willReturn($changes);
        $factory->createProductDraft($product, 'Mary')->willReturn($partialDraft);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_PARTIAL_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $applier->applyAllChanges($product, $partialDraft)->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();
        $productDraft->removeChange('name', null, null)->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(true);
        $remover->remove(Argument::cetera())->shouldNotBeCalled();
        $saver->save($productDraft)->shouldBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_PARTIAL_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->partialApprove($productDraft, $attribute);
    }

    function it_applies_changes_and_removes_the_draft_when_partially_approve_a_product_draft(
        $workingCopySaver,
        $factory,
        $applier,
        $dispatcher,
        $saver,
        $remover,
        ProductDraftInterface $productDraft,
        ProductDraftInterface $partialDraft,
        AttributeInterface $attribute,
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('Mary');
        $productDraft->getChange('name', null, null)->willReturn('new name');
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $attribute->getLabel()->willReturn('Name');
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $changes = [
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'new name']
                ]
            ]
        ];
        $partialDraft->setChanges($changes)->shouldBeCalled();
        $partialDraft->getChanges()->willReturn($changes);
        $factory->createProductDraft($product, 'Mary')->willReturn($partialDraft);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_PARTIAL_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $applier->applyAllChanges($product, $partialDraft)->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();
        $productDraft->removeChange('name', null, null)->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(false);
        $remover->remove($productDraft, ['flush' => false])->shouldBeCalled();
        $saver->save($productDraft)->shouldNotBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_PARTIAL_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->partialApprove($productDraft, $attribute, $channel, $locale);
    }

    function it_marks_a_change_as_draft_on_partial_reject(
        $dispatcher,
        $saver,
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('name');
        $attribute->getLabel()->willReturn('Name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_PARTIAL_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $productDraft
            ->setReviewStatusForChange(ProductDraftInterface::CHANGE_DRAFT, 'name', null, null)
            ->shouldBeCalled();
        $saver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_PARTIAL_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->partialRefuse($productDraft, $attribute);
    }

    function it_marks_a_product_draft_as_in_progress_when_refusing_it(
        $dispatcher,
        $saver,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_DRAFT)->shouldBeCalled();
        $saver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->refuse($productDraft);
    }

    function it_finds_a_product_draft_when_it_already_exists(
        $userContext,
        $repository,
        UserInterface $user,
        ProductInterface $product,
        ProductDraftInterface $productDraft
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProductDraft($product, 'peter')->willReturn($productDraft);

        $this->findOrCreate($product);
    }

    function it_creates_a_product_draft_when_it_does_not_exist(
        $userContext,
        $repository,
        $factory,
        UserInterface $user,
        ProductInterface $product,
        ProductDraftInterface $productDraft
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserProductDraft($product, 'peter')->willReturn(null);
        $factory->createProductDraft($product, 'peter')->willReturn($productDraft);

        $this->findOrCreate($product)->shouldReturn($productDraft);
    }

    function it_throws_exception_when_find_product_draft_and_current_cannot_be_resolved(
        $userContext,
        ProductInterface $product
    ) {
        $userContext->getUser()->willReturn(null);

        $this
            ->shouldThrow(new \LogicException('Current user cannot be resolved'))
            ->duringFindOrCreate($product, 'fr_FR');
    }

    function it_marks_product_draft_as_ready($dispatcher, $saver, ProductDraftInterface $productDraft)
    {
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_READY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $saver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_READY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->markAsReady($productDraft);
    }

    function it_throws_an_exception_when_trying_to_partially_approve_a_scopable_attribute_without_channel(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(true);

        $this->shouldThrow('\LogicException')->during('partialApprove', [$productDraft, $attribute, null, $locale]);
    }

    function it_throws_an_exception_when_trying_to_partially_approve_a_localizable_attribute_without_locale(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);

        $this->shouldThrow('\LogicException')->during('partialApprove', [$productDraft, $attribute, $channel]);
    }

    function it_removes_a_product_draft($dispatcher, $remover, ProductDraftInterface $productDraft)
    {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $remover->remove($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->remove($productDraft);
    }

    function it_throws_an_exception_when_trying_to_remove_a_product_draft_in_ready_state(
        $remover,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $remover->remove($productDraft)->shouldNotBeCalled();
        $this->shouldThrow('\LogicException')->during('remove', [$productDraft]);
    }
}
