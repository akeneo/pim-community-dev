<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal\InMemoryProposalUpsert;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsertSpec extends ObjectBehavior
{
    /**
     * @param ObjectUpdaterInterface|\PhpSpec\Wrapper\Collaborator $productUpdater
     */
    public function let(ObjectUpdaterInterface $productUpdater): void
    {
        $this->beConstructedWith($productUpdater);
    }

    public function it_is_an_in_memory_proposal_upsert(): void
    {
        $this->shouldHaveType(InMemoryProposalUpsert::class);
        $this->shouldImplement(ProposalUpsertInterface::class);
    }

    public function it_stores_updated_values(
        $productUpdater,
        ProductInterface $product,
        ValueCollectionInterface $values
    ): void {
        $suggestedData = ['foo' => 'bar'];

        $values->toArray()->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $product->getIdentifier()->willReturn('test');
        $product->getValues()->willReturn($values);
        $productUpdater->update($product, ['values' => $suggestedData])->shouldBeCalled();

        $this->process($product, $suggestedData, 'an_author')->shouldReturn(null);
        $this->hasProposalForProduct('test', 'an_author')->shouldReturn(true);
    }
}
