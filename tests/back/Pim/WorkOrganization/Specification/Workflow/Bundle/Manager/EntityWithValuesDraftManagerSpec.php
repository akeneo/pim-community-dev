<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Applier\DraftApplierInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\DraftNotReviewableException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\EntityWithValuesDraftFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EntityWithValuesDraftManagerSpec extends ObjectBehavior
{
    function let(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        EntityWithValuesDraftFactory $factory,
        EntityWithValuesDraftRepositoryInterface $repository,
        DraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $draftSaver,
        RemoverInterface $remover,
        CollectionFilterInterface $valuesFilter,
        PimUserDraftSourceFactory $draftSourceFactory
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
            $valuesFilter,
            $draftSourceFactory
        );
    }

    function it_throws_an_exception_when_trying_to_approve_a_change_on_a_non_ready_draft(
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->shouldThrow(DraftNotReviewableException::class)->during('approveChange', [$draft, $attribute]);
    }

    function it_approves_a_change(
        $dispatcher,
        $valuesFilter,
        $factory,
        $applier,
        $workingCopySaver,
        $remover,
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute,
        ProductInterface $product,
        EntityWithValuesDraftInterface $partialDraft,
        WriteValueCollection $values
    ) {
        $source = 'source';
        $sourceLabel = 'Source';
        $author = 'author';
        $authorLabel = 'Author';

        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $draft->getEntityWithValue()->willReturn($product);
        $draft->getAuthor()->willReturn($author);
        $draft->getAuthorLabel()->willReturn($authorLabel);
        $draft->getSource()->willReturn($source);
        $draft->getSourceLabel()->willReturn($sourceLabel);
        $draft->getValues()->willReturn($values);
        $values->getByCodes('sku', null, null)->willReturn(null);
        $attribute->getCode()->willReturn('sku');
        $partialDraft->getEntityWithValue()->willReturn($product);

        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::PRE_PARTIAL_APPROVE)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::POST_PARTIAL_APPROVE)->shouldBeCalled();

        $draft->getChange('sku', null, null)->willReturn(['ak-mug']);
        $wholeChange = ['sku' => [['locale' => null, 'scope' => null, 'data' => ['ak-mug']]]];

        $valuesFilter->filterCollection(
            $wholeChange,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($wholeChange);

        $factory->createEntityWithValueDraft($product, Argument::type(DraftSource::class))
            ->shouldBeCalled()
            ->willReturn($partialDraft);

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
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute
    ) {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->shouldThrow(DraftNotReviewableException::class)->during('refuseChange', [$draft, $attribute]);
    }

    function it_refuses_a_change(
        $dispatcher,
        $valuesFilter,
        $workingCopySaver,
        EntityWithValuesDraftInterface $draft,
        AttributeInterface $attribute,
        ProductInterface $product,
        EntityWithValuesDraftInterface $partialDraft,
        WriteValueCollection $values,
        ValueInterface $value
    ) {
        $source = 'source';
        $sourceLabel = 'Source';
        $author = 'author';
        $authorLabel = 'Author';

        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $draft->getEntityWithValue()->willReturn($product);
        $draft->getAuthor()->willReturn($author);
        $draft->getAuthorLabel()->willReturn($authorLabel);
        $draft->getSource()->willReturn($source);
        $draft->getSourceLabel()->willReturn($sourceLabel);
        $draft->getValues()->willReturn($values);
        $values->getByCodes('sku', null, null)->willReturn($value);
        $attribute->getCode()->willReturn('sku');
        $partialDraft->getEntityWithValue()->willReturn($product);

        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::PRE_PARTIAL_REFUSE)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::POST_PARTIAL_REFUSE)->shouldBeCalled();

        $wholeChange = ['sku' => [['locale' => null, 'scope' => null]]];

        $valuesFilter->filterCollection(
            $wholeChange,
            'pim.internal_api.attribute.edit'
        )->shouldBeCalled()->willReturn($wholeChange);

        $draft->setReviewStatusForChange(EntityWithValuesDraftInterface::CHANGE_DRAFT, 'sku', null, null )->shouldBeCalled();
        $workingCopySaver->save($draft);

        $this->refuseChange($draft, $attribute);
    }

    function it_throws_an_exception_when_trying_to_approve_a_whole_non_ready_draft(EntityWithValuesDraftInterface $draft)
    {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->shouldThrow(DraftNotReviewableException::class)->during('approve', [$draft]);
    }

    function it_approves_a_whole_draft_with_all_changes_approvable(
        $dispatcher,
        $valuesFilter,
        $factory,
        $applier,
        $workingCopySaver,
        $remover,
        EntityWithValuesDraftInterface $draft,
        ProductInterface $product,
        WriteValueCollection $values
    ) {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $draft->getEntityWithValue()->willReturn($product);
        $draft->getValues()->willReturn($values);
        $values->getByCodes('description', 'ecommerce', 'en_US')->willReturn(null);
        $values->getByCodes('description', 'tablet', 'fr_FR')->willReturn(null);
        $values->getByCodes('sku', null, null)->willReturn(null);

        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::PRE_APPROVE)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::POST_APPROVE)->shouldBeCalled();

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

        $factory->createEntityWithValueDraft(Argument::cetera())->shouldNotBeCalled();

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
        EntityWithValuesDraftInterface $draft,
        ProductInterface $product,
        EntityWithValuesDraftInterface $partialDraft,
        WriteValueCollection $values
    ) {
        $source = 'source';
        $sourceLabel = 'Source';
        $author = 'author';
        $authorLabel = 'Author';

        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $draft->getEntityWithValue()->willReturn($product);
        $draft->getAuthor()->willReturn($author);
        $draft->getAuthorLabel()->willReturn($authorLabel);
        $draft->getSource()->willReturn($source);
        $draft->getSourceLabel()->willReturn($sourceLabel);
        $draft->getValues()->willReturn($values);
        $values->getByCodes('sku', null, null)->willReturn(null);
        $values->getByCodes('description', 'tablet', 'fr_FR')->willReturn(null);
        $partialDraft->getEntityWithValue()->willReturn($product);

        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::PRE_APPROVE)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::POST_APPROVE)->shouldBeCalled();

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

        $factory->createEntityWithValueDraft($product, Argument::type(DraftSource::class))
            ->shouldBeCalled()
            ->willReturn($partialDraft);
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

    function it_throws_an_exception_when_trying_to_refuse_a_whole_non_ready_draft(EntityWithValuesDraftInterface $draft)
    {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $this->shouldThrow(DraftNotReviewableException::class)->during('refuse', [$draft]);
    }

    function it_refuses_a_whole_draft(
        $dispatcher,
        $valuesFilter,
        $draftSaver,
        EntityWithValuesDraftInterface $draft,
        WriteValueCollection $values
    ) {
        $draft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $draft->getValues()->willReturn($values);
        $values->getByCodes('sku', null, null)->willReturn(null);
        $values->getByCodes('description', 'tablet', 'fr_FR')->willReturn(null);

        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::PRE_REFUSE)->shouldBeCalled();
        $dispatcher->dispatch(Argument::any(), EntityWithValuesDraftEvents::POST_REFUSE)->shouldBeCalled();

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

        $draft->setReviewStatusForChange(EntityWithValuesDraftInterface::CHANGE_DRAFT, 'sku', null, null )->shouldBeCalled();
        $draft->setReviewStatusForChange(EntityWithValuesDraftInterface::CHANGE_DRAFT, 'description', 'fr_FR', 'tablet' )->shouldBeCalled();
        $draft->setReviewStatusForChange(EntityWithValuesDraftInterface::CHANGE_DRAFT, 'description', 'en_US', 'ecommerce' )->shouldNotBeCalled();

        $draftSaver->save($draft)->shouldBeCalled();

        $this->refuse($draft);
    }

    function it_finds_a_product_draft_when_it_already_exists(
        $userContext,
        $repository,
        UserInterface $user,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $user->getUsername()->willReturn('peter');
        $userContext->getUser()->willReturn($user);
        $repository->findUserEntityWithValuesDraft($product, 'peter')->willReturn($productDraft);

        $this->findOrCreate($product);
    }

    function it_creates_a_product_draft_when_it_does_not_exist(
        $userContext,
        $repository,
        $factory,
        $draftSourceFactory,
        UserInterface $user,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        DraftSource $draftSource
    ) {
        $username = 'peter';
        $userFullName = 'Peter Williams';
        $source = 'source';
        $sourceLabel = 'Source';

        $user->getUsername()->willReturn($username);
        $userContext->getUser()->willReturn($user);
        $repository->findUserEntityWithValuesDraft($product, $username)->willReturn(null);

        $draftSource->getSource()->willReturn($source);
        $draftSource->getSourceLabel()->willReturn($sourceLabel);
        $draftSource->getAuthor()->willReturn($username);
        $draftSource->getAuthorLabel()->willReturn($userFullName);

        $draftSourceFactory->createFromUser($user)->willReturn($draftSource);

        $factory->createEntityWithValueDraft($product, $draftSource)
            ->willReturn($productDraft);

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

    function it_marks_product_draft_as_ready($dispatcher, $draftSaver, EntityWithValuesDraftInterface $productDraft)
    {
        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::PRE_READY
            )
            ->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $draftSaver->save($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::POST_READY
            )
            ->shouldBeCalled();

        $this->markAsReady($productDraft);
    }

    function it_removes_a_product_draft(
        $valuesFilter,
        $dispatcher,
        $remover,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $values = [
            'description' => [
                ['locale' => 'fr_FR', 'scope' => 'tablet', 'data' => 'bar'],
                ['locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'foo']
            ]
        ];
        $valuesFilter->filterCollection($values, 'pim.internal_api.attribute.edit')->willReturn($values);
        $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT)->willReturn(['values' => $values]);

        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::PRE_REMOVE
            )
            ->shouldBeCalled();
        $remover->remove($productDraft)->shouldBeCalled();
        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::POST_REMOVE
            )
            ->shouldBeCalled();

        $this->remove($productDraft);
    }

    function it_partially_removes_draft_changes(
        $valuesFilter,
        $dispatcher,
        $remover,
        $draftSaver,
        EntityWithValuesDraftInterface $productDraft,
        WriteValueCollection $values
    ) {
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::IN_PROGRESS);
        $productDraft->getValues()->willReturn($values);
        $values->getByCodes('sku', null, null)->willReturn(null);
        $values->getByCodes('description', 'tablet', 'fr_FR')->willReturn(null);
        $values->getByCodes('description', 'mobile', 'fr_FR')->willReturn(null);

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
        $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT)->willReturn(['values' => $values]);

        $productDraft->removeChange('description', 'fr_FR', 'mobile')->shouldBeCalled();
        $productDraft->hasChanges()->willReturn(true);

        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::PRE_REMOVE
            )
            ->shouldBeCalled();
        $remover->remove($productDraft)->shouldNotBeCalled();
        $dispatcher
            ->dispatch(
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent'),
                EntityWithValuesDraftEvents::POST_REMOVE
            )
            ->shouldBeCalled();

        $draftSaver->save($productDraft)->shouldBeCalled();

        $this->remove($productDraft);
    }

    function it_throws_an_exception_when_trying_to_remove_a_product_draft_in_ready_state(
        $remover,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $remover->remove($productDraft)->shouldNotBeCalled();
        $this->shouldThrow(DraftNotReviewableException::class)->during('remove', [$productDraft]);
    }

    function it_throws_an_exception_when_trying_to_approve_a_single_change_without_permission(
        $valuesFilter,
        AttributeInterface $attribute,
        EntityWithValuesDraftInterface $productDraft,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('name');
        $locale->getCode()->willReturn('fr_FR');
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productDraft->getChange('name', 'fr_FR', null)->willReturn(['Le nouveau nom']);

        $valuesFilter->filterCollection([
            'name' => [
                ['locale' => 'fr_FR', 'scope' => null, 'data' => ['Le nouveau nom']]
            ]
        ], 'pim.internal_api.attribute.edit')->willReturn([]);

        $this->shouldThrow(DraftNotReviewableException::class)->during('approveChange', [
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
        EntityWithValuesDraftInterface $productDraft,
        LocaleInterface $locale
    ) {
        $attribute->getCode()->willReturn('name');
        $locale->getCode()->willReturn('fr_FR');
        $productDraft->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);

        $valuesFilter->filterCollection([
            'name' => [
                ['locale' => 'fr_FR', 'scope' => null]
            ]
        ], 'pim.internal_api.attribute.edit')->willReturn([]);

        $this->shouldThrow(DraftNotReviewableException::class)->during('refuseChange', [
            $productDraft,
            $attribute,
            $locale,
            null,
            []
        ]);
    }
}
