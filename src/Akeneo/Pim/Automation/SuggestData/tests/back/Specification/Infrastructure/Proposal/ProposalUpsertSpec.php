<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal\ProposalUpsert;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalUpsertSpec extends ObjectBehavior
{
    public function let(
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerClearerInterface $cacheClearer
    ): void {
        $this->beConstructedWith($productUpdater, $draftBuilder, $draftSaver, $eventDispatcher, $cacheClearer);
    }

    public function it_is_a_create_proposal(): void
    {
        $this->shouldHaveType(ProposalUpsert::class);
        $this->shouldImplement(ProposalUpsertInterface::class);
    }

    public function it_creates_proposals_from_suggested_data(
        $productUpdater,
        $draftBuilder,
        $draftSaver,
        $eventDispatcher,
        $cacheClearer,
        ProductInterface $product,
        ProductInterface $otherProduct,
        EntityWithValuesDraftInterface $productDraft,
        EntityWithValuesDraftInterface $otherProductDraft
    ): void {
        $product->getId()->willReturn(42);
        $suggestedData = ['foo' => 'bar'];
        $draftBuilder->build($product, 'Franklin')->willReturn($productDraft);

        $otherProduct->getId()->willReturn(56);
        $otherSuggestedData = ['test' => 42];
        $draftBuilder->build($otherProduct, 'Franklin')->willReturn($otherProductDraft);

        $productUpdater->update($product, ['values' => $suggestedData])->shouldBeCalled();
        $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $productDraft->markAsReady()->shouldBeCalled();
        $draftSaver->save($productDraft)->shouldBeCalled();

        $productUpdater->update($otherProduct, ['values' => $otherSuggestedData])->shouldBeCalled();
        $otherProductDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW)->shouldBeCalled();
        $otherProductDraft->markAsReady()->shouldBeCalled();
        $draftSaver->save($otherProductDraft)->shouldBeCalled();

        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::PRE_READY,
            Argument::type(GenericEvent::class)
        )->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(
            EntityWithValuesDraftEvents::POST_READY,
            Argument::type(GenericEvent::class)
        )->shouldBeCalledTimes(2);

        $eventDispatcher->dispatch(
            SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED,
            new GenericEvent([42, 56])
        )->shouldBeCalledOnce();
        $cacheClearer->clear()->shouldBeCalledOnce();

        $this->process(
            [
                new ProposalSuggestedData($suggestedData, $product->getWrappedObject()),
                new ProposalSuggestedData($otherSuggestedData, $otherProduct->getWrappedObject()),
            ],
            'Franklin'
        )->shouldReturn(null);
    }

    public function it_skips_the_proposal_creation_if_there_is_an_error(
        $productUpdater,
        $draftBuilder,
        $eventDispatcher,
        ProductInterface $product,
        ProductInterface $otherProduct
    ): void {
        $product->getId()->willReturn(42);
        $suggestedData = ['foo' => 'bar'];
        $productUpdater->update($product, ['values' => $suggestedData])->willThrow(new \LogicException());
        $draftBuilder->build($product, 'Franklin')->shouldNotBeCalled();

        $otherProduct->getId()->willReturn(56);
        $otherSuggestedData = ['test' => 42];
        $productUpdater->update($otherProduct, ['values' => $otherSuggestedData])->willReturn($otherProduct);
        $draftBuilder->build($otherProduct, 'Franklin')->willThrow(new \LogicException());

        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $this->process(
            [
                new ProposalSuggestedData($suggestedData, $product->getWrappedObject()),
                new ProposalSuggestedData($otherSuggestedData, $otherProduct->getWrappedObject()),
            ],
            'Franklin'
        )->shouldReturn(null);
    }
}
