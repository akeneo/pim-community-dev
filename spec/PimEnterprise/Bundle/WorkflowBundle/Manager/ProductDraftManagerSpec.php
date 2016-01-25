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

    function it_does_not_remove_new_draft_on_approve(
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
        $productDraft->getId()->willReturn(null);

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
        $remover->remove(Argument::cetera())->shouldNotBeCalled();

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
        $remover,
        ProductDraftInterface $productDraft,
        ProductDraftInterface $temporaryDraft,
        AttributeInterface $attribute,
        ProductInterface $product
    ) {
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('Mary');
        $productDraft->getChange('name', null, null)->willReturn('new name');
        $productDraft->removeChange('name', null, null)->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(true);

        $attribute->getLabel()->willReturn('Name');
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $changes = [
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'new name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'status' => ProductDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ];
        $temporaryDraft->setChanges($changes)->shouldBeCalled();
        $temporaryDraft->getChanges()->willReturn($changes);
        $temporaryDraft->getProduct()->willReturn($product);
        $temporaryDraft->getId()->willReturn(null);

        $factory->createProductDraft($product, 'Mary')->willReturn($temporaryDraft);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $applier->applyToReviewChanges($product, $temporaryDraft)->shouldBeCalled();
        $temporaryDraft->removeChange('name', null, null)->shouldBeCalled();
        $temporaryDraft->hasChanges()->willReturn(false);
        $workingCopySaver->save($product)->shouldBeCalled();
        $remover->remove(Argument::cetera())->shouldNotBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_APPROVE,
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
        $remover,
        ProductDraftInterface $productDraft,
        ProductDraftInterface $temporaryDraft,
        AttributeInterface $attribute,
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $productDraft->getProduct()->willReturn($product);
        $productDraft->getAuthor()->willReturn('Mary');
        $productDraft->getChange('name', null, null)->willReturn('new name');
        $productDraft->removeChange('name', null, null)->shouldBeCalled();

        $attribute->getLabel()->willReturn('Name');
        $attribute->getCode()->willReturn('name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $changes = [
            'values' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'data' => 'new name']
                ]
            ],
            'review_statuses' => [
                'name' => [
                    ['scope' => null, 'locale' => null, 'status' => ProductDraftInterface::CHANGE_TO_REVIEW]
                ]
            ]
        ];
        $temporaryDraft->setChanges($changes)->shouldBeCalled();
        $temporaryDraft->getChanges()->willReturn($changes);
        $temporaryDraft->getProduct()->willReturn($product);
        $temporaryDraft->getId()->willReturn(null);

        $factory->createProductDraft($product, 'Mary')->willReturn($temporaryDraft);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $applier->applyToReviewChanges($product, $temporaryDraft)->shouldBeCalled();
        $temporaryDraft->removeChange('name', null, null)->shouldBeCalled();
        $temporaryDraft->hasChanges()->willReturn(false);
        $productDraft->hasChanges()->willReturn(false);
        $workingCopySaver->save($product)->shouldBeCalled();
        $remover->remove($productDraft, ['flush' => false])->shouldBeCalled();

        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_APPROVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->partialApprove($productDraft, $attribute, $channel, $locale);
    }

    function it_marks_a_change_as_rejected_on_partial_reject(
        ProductDraftInterface $productDraft,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('name');
        $attribute->getLabel()->willReturn('Name');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $productDraft
            ->setReviewStatusForChange(ProductDraftInterface::CHANGE_REJECTED, 'name', null, null)
            ->shouldBeCalled();

        $this->partialReject($productDraft, $attribute);
    }

    function it_marks_as_in_progress_product_draft_which_is_ready_when_refusing_it(
        $dispatcher,
        $saver,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->isInProgress()->willReturn(false);
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS)->shouldBeCalled();
        $saver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_REFUSE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->refuse($productDraft);
    }

    function it_removes_in_progress_product_draft_when_refusing_it($saver, ProductDraftInterface $productDraft)
    {
        $productDraft->isInProgress()->willReturn(true);
        $saver->save($productDraft);

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
        $productDraft->setStatus(ProductDraftInterface::READY)->shouldBeCalled();
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
}
