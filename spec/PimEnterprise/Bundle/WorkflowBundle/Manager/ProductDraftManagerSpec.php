<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
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
        SaverInterface $draftSaver,
        RemoverInterface $remover,
        CollectionFilterInterface $valuesFilter
    ) {
        $this->beConstructedWith(
            $workingCopySaver,
            $userContext,
            $factory,
            $repository,
            $applier,
            $dispatcher,
            $draftSaver,
            $remover,
            $valuesFilter
        );
    }

    function it_throws_an_exception_when_trying_to_approve_a_change_on_a_non_ready_draft(
        ProductDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('approveChange', [$draft, $attribute]);
    }

    function it_approves_a_change(
        $dispatcher,
        $valuesFilter,
        $factory,
        $applier,
        $workingCopySaver,
        $remover,
        ProductDraftInterface $draft,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductDraftInterface $partialDraft
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::READY);
        $draft->getProduct()->willReturn($product);
        $draft->getAuthor()->willReturn('author');
        $attribute->getCode()->willReturn('sku');
        $partialDraft->getProduct()->willReturn($product);

        $dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_APPROVE, Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(ProductDraftEvents::POST_PARTIAL_APPROVE, Argument::any())->shouldBeCalled();

        $draft->getChange('sku', null, null)->willReturn('ak-mug');
        $wholeChange = ['sku' => [['locale' => null, 'scope' => null, 'data' => 'ak-mug']]];

        $valuesFilter->filterCollection(
            $wholeChange,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($wholeChange);

        $factory->createProductDraft($product, 'author')->shouldBeCalled()->willReturn($partialDraft);
        $partialDraft->setChanges(['values' => $wholeChange])->shouldBeCalled();
        $partialDraft->getId()->willReturn(null);

        $applier->applyAllChanges($product, $partialDraft)->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();

        $draft->removeChange('sku', null, null)->willReturn(false);
        $draft->hasChanges()->willReturn(false);

        $remover->remove($draft, Argument::any())->shouldBeCalled();

        $this->approveChange($draft, $attribute);
    }

    function it_throws_an_exception_when_trying_to_refuse_a_change_on_a_non_ready_draft(
        ProductDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('refuseChange', [$draft, $attribute]);
    }

    function it_refuses_a_change(
        $dispatcher,
        $valuesFilter,
        $workingCopySaver,
        ProductDraftInterface $draft,
        AttributeInterface $attribute,
        ProductInterface $product,
        ProductDraftInterface $partialDraft
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::READY);
        $draft->getProduct()->willReturn($product);
        $draft->getAuthor()->willReturn('author');
        $attribute->getCode()->willReturn('sku');
        $partialDraft->getProduct()->willReturn($product);

        $dispatcher->dispatch(ProductDraftEvents::PRE_PARTIAL_REFUSE, Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(ProductDraftEvents::POST_PARTIAL_REFUSE, Argument::any())->shouldBeCalled();

        $wholeChange = ['sku' => [['locale' => null, 'scope' => null]]];

        $valuesFilter->filterCollection(
            $wholeChange,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($wholeChange);

        $draft->setReviewStatusForChange(ProductDraftInterface::CHANGE_DRAFT, 'sku', null, null )->shouldBeCalled();
        $workingCopySaver->save($draft);

        $this->refuseChange($draft, $attribute);
    }

    function it_throws_an_exception_when_trying_to_approve_a_whole_non_ready_draft(ProductDraftInterface $draft)
    {
        $draft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('approve', [$draft]);
    }

    function it_approves_a_whole_draft_with_all_changes_approvable(
        $dispatcher,
        $valuesFilter,
        $factory,
        $applier,
        $workingCopySaver,
        $remover,
        ProductDraftInterface $draft,
        ProductInterface $product
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::READY);
        $draft->getProduct()->willReturn($product);

        $dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, Argument::any())->shouldBeCalled();

        $wholeChanges = [
            'sku' => [['locale' => null, 'scope' => null]],
            'description' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'foo'],
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar']
            ]
        ];

        $draft->getChangesToReview()->willReturn(['values' => $wholeChanges]);
        $valuesFilter->filterCollection(
            $wholeChanges,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($wholeChanges);

        $factory->createProductDraft(Argument::cetera())->shouldNotBeCalled();

        $draft->getId()->willReturn(12);
        $applier->applyToReviewChanges($product, $draft)->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();

        $draft->removeChange('sku', null, null)->shouldBeCalled();
        $draft->removeChange('description', 'en_US', 'ecommerce')->shouldBeCalled();
        $draft->removeChange('description', 'fr_FR', 'tablet')->shouldBeCalled();

        $draft->hasChanges()->willReturn(false);
        $remover->remove($draft, Argument::any())->shouldBeCalled();

        $this->approve($draft);
    }

    function it_approves_a_whole_draft_with_some_changes_not_approvable(
        $dispatcher,
        $valuesFilter,
        $factory,
        $applier,
        $workingCopySaver,
        $remover,
        $draftSaver,
        ProductDraftInterface $draft,
        ProductInterface $product,
        ProductDraftInterface $partialDraft
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::READY);
        $draft->getProduct()->willReturn($product);
        $draft->getAuthor()->willReturn('author');
        $partialDraft->getProduct()->willReturn($product);

        $dispatcher->dispatch(ProductDraftEvents::PRE_APPROVE, Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(ProductDraftEvents::POST_APPROVE, Argument::any())->shouldBeCalled();

        $wholeChanges = [
            'sku' => [['locale' => null, 'scope' => null]],
            'description' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'foo'],
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar']
            ]
        ];
        $approvableChanges = [
            'sku' => [['locale' => null, 'scope' => null]],
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar']
            ]
        ];

        $draft->getChangesToReview()->willReturn(['values' => $wholeChanges]);
        $valuesFilter->filterCollection(
            $wholeChanges,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($approvableChanges);

        $factory->createProductDraft($product, 'author')->shouldBeCalled()->willReturn($partialDraft);
        $partialDraft->getId()->willReturn(null);
        $partialDraft->setChanges(['values' => $approvableChanges])->shouldBeCalled();

        $applier->applyAllChanges($product, $partialDraft)->shouldBeCalled();
        $workingCopySaver->save($product)->shouldBeCalled();

        $draft->removeChange('sku', null, null)->shouldBeCalled();
        $draft->removeChange('description', 'fr_FR', 'tablet')->shouldBeCalled();
        $draft->removeChange('description', 'en_US', 'ecommerce')->shouldNotBeCalled();

        $draft->hasChanges()->willReturn(true);
        $remover->remove(Argument::cetera())->shouldNotBeCalled();
        $draftSaver->save($draft)->shouldBeCalled();

        $this->approve($draft);
    }

    function it_throws_an_exception_when_trying_to_refuse_a_whole_non_ready_draft(ProductDraftInterface $draft)
    {
        $draft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('refuse', [$draft]);
    }

    function it_refuses_a_whole_draft(
        $dispatcher,
        $valuesFilter,
        $draftSaver,
        ProductDraftInterface $draft
    ) {
        $draft->getStatus()->willReturn(ProductDraftInterface::READY);

        $dispatcher->dispatch(ProductDraftEvents::PRE_REFUSE, Argument::any())->shouldBeCalled();
        $dispatcher->dispatch(ProductDraftEvents::POST_REFUSE, Argument::any())->shouldBeCalled();

        $wholeChanges = [
            'sku' => [['locale' => null, 'scope' => null]],
            'description' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'foo'],
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar']
            ]
        ];
        $refusableChanges = [
            'sku' => [['locale' => null, 'scope' => null]],
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar']
            ]
        ];

        $draft->getChangesToReview()->willReturn(['values' => $wholeChanges]);
        $valuesFilter->filterCollection(
            $wholeChanges,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($refusableChanges);

        $draft->setReviewStatusForChange(ProductDraftInterface::CHANGE_DRAFT, 'sku', null, null )->shouldBeCalled();
        $draft->setReviewStatusForChange(ProductDraftInterface::CHANGE_DRAFT, 'description', 'fr_FR', 'tablet' )->shouldBeCalled();
        $draft->setReviewStatusForChange(ProductDraftInterface::CHANGE_DRAFT, 'description', 'en_US', 'ecommerce' )->shouldNotBeCalled();

        $draftSaver->save($draft)->shouldBeCalled();

        $this->refuse($draft);
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

    function it_marks_product_draft_as_ready($dispatcher, $draftSaver, ProductDraftInterface $productDraft)
    {
        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_READY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $productDraft->setAllReviewStatuses(ProductDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $draftSaver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_READY,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $this->markAsReady($productDraft);
    }

    function it_removes_a_product_draft(
        $valuesFilter,
        $dispatcher,
        $remover,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $values = [
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar'],
                ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'foo']
            ]
        ];
        $valuesFilter->filterCollection($values, 'pim.internal_api.attribute.edit')->willReturn($values);
        $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_DRAFT)->willReturn(['values' => $values]);

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

    function it_partially_removes_draft_changes(
        $valuesFilter,
        $dispatcher,
        $remover,
        $draftSaver,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::IN_PROGRESS);
        $values = [
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar'],
                ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'foo']
            ]
        ];
        $valuesFilter->filterCollection($values, 'pim.internal_api.attribute.edit')->willReturn([
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'foo']
            ]
        ]);
        $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_DRAFT)->willReturn(['values' => $values]);

        $productDraft->removeChange('description', 'fr_FR', 'mobile')->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(true);

        $dispatcher
            ->dispatch(
                ProductDraftEvents::PRE_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $remover->remove($productDraft)->shouldNotBeCalled();
        $dispatcher
            ->dispatch(
                ProductDraftEvents::POST_REMOVE,
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();

        $draftSaver->save($productDraft)->shouldBeCalled();

        $this->remove($productDraft);
    }

    function it_throws_an_exception_when_trying_to_remove_a_product_draft_in_ready_state(
        $remover,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $remover->remove($productDraft)->shouldNotBeCalled();
        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('remove', [$productDraft]);
    }

    function it_throws_an_exception_when_trying_to_approve_a_single_change_without_permission(
        $valuesFilter,
        AttributeInterface $attribute,
        ProductDraftInterface $productDraft,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('name');
        $locale->getCode()->willReturn('fr_FR');
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);
        $productDraft->getChange('name', 'fr_FR', null)->willReturn('Le nouveau nom');

        $valuesFilter->filterCollection([
            'name' => [
                ['locale' => 'fr_FR', 'scope' => null, 'data' => 'Le nouveau nom']
            ]
        ], 'pim.internal_api.attribute.edit')->willReturn([]);

        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('approveChange', [
            $productDraft,
            $attribute,
            $locale,
            null,
            []
        ]);
    }

    function it_throws_an_exception_when_trying_to_refuse_a_single_change_without_permission(
        $valuesFilter,
        AttributeInterface $attribute,
        ProductDraftInterface $productDraft,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('name');
        $locale->getCode()->willReturn('fr_FR');
        $productDraft->getStatus()->willReturn(ProductDraftInterface::READY);

        $valuesFilter->filterCollection([
            'name' => [
                ['locale' => 'fr_FR', 'scope' => null]
            ]
        ], 'pim.internal_api.attribute.edit')->willReturn([]);

        $this->shouldThrow('PimEnterprise\Bundle\WorkflowBundle\Exception\DraftNotReviewableException')->during('refuseChange', [
            $productDraft,
            $attribute,
            $locale,
            null,
            []
        ]);
    }
}
