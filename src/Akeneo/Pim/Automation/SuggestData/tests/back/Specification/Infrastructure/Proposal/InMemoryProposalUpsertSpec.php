<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Event\SubscriptionEvents;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal\InMemoryProposalUpsert;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsertSpec extends ObjectBehavior
{
    public function let(ObjectUpdaterInterface $productUpdater, EventDispatcherInterface $eventDispatcher): void
    {
        $this->beConstructedWith($productUpdater, $eventDispatcher);
    }

    public function it_is_an_in_memory_proposal_upsert(): void
    {
        $this->shouldHaveType(InMemoryProposalUpsert::class);
        $this->shouldImplement(ProposalUpsertInterface::class);
    }

    public function it_stores_updated_values(
        $productUpdater,
        $eventDispatcher,
        ProductInterface $product,
        Productinterface $product2,
        ValueCollectionInterface $values,
        ValueCollectionInterface $values2
    ): void {
        $values->toArray()->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $product->getIdentifier()->willReturn('test');
        $product->getValues()->willReturn($values);
        $suggestedData = new SuggestedData('subscription-1', ['foo' => 'bar'], $product->getWrappedObject());

        $values2->toArray()->willReturn(['test' => 0]);
        $product2->getIdentifier()->willReturn('test2');
        $product2->getValues()->willReturn($values2);
        $suggestedData2 = new SuggestedData('subscription-2', ['test' => 42], $product2->getWrappedObject());

        $productUpdater->update($product, ['values' => ['foo' => 'bar']])->shouldBeCalled();
        $productUpdater->update($product2, ['values' => ['test' => 42]])->shouldBeCalled();

        $eventDispatcher->dispatch(
            SubscriptionEvents::FRANKLIN_PROPOSALS_CREATED,
            new GenericEvent(['subscription-1', 'subscription-2'])
        )->shouldBeCalled();

        $this->process([$suggestedData, $suggestedData2], 'an_author')->shouldReturn(null);

        $this->hasProposalForProduct('test', 'an_author')->shouldReturn(true);
        $this->hasProposalForProduct('test2', 'an_author')->shouldReturn(true);
    }
}
