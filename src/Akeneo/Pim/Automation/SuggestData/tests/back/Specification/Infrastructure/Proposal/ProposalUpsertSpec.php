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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\SuggestedData;
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
        $suggestedData = ['foo' => 'bar'];
        $draftBuilder->build($product, 'PIM.ai')->willReturn($productDraft);

        $otherSuggestedData = ['test' => 42];
        $draftBuilder->build($otherProduct, 'PIM.ai')->willReturn($otherProductDraft);

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

        $eventDispatcher->dispatch(SubscriptionEvents::PROPOSALS_CREATED, Argument::type(GenericEvent::class))
                        ->shouldBeCalledOnce();
        $cacheClearer->clear()->shouldBeCalledOnce();

        $this->process(
            [
                new SuggestedData('123456', $suggestedData, $product->getWrappedObject()),
                new SuggestedData('654321', $otherSuggestedData, $otherProduct->getWrappedObject()),
            ],
            'PIM.ai'
        )->shouldReturn(null);
    }
}
