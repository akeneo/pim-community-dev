<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\InMemoryProposalUpsert;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InMemoryProposalUpsertSpec extends ObjectBehavior
{
    public function let(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        EventDispatcherInterface $eventDispatcher
    ): void {
        $this->beConstructedWith($productRepository, $productUpdater, $eventDispatcher);
    }

    public function it_is_an_in_memory_proposal_upsert(): void
    {
        $this->shouldHaveType(InMemoryProposalUpsert::class);
        $this->shouldImplement(ProposalUpsertInterface::class);
    }

    public function it_stores_updated_values(
        $productRepository,
        $productUpdater,
        $eventDispatcher,
        ProductInterface $product,
        Productinterface $product2,
        FamilyInterface $family,
        ValueCollectionInterface $values,
        ValueCollectionInterface $values2
    ): void {
        $family->getAttributeCodes()->willReturn(['foo', 'test']);

        $productRepository->find(343)->willReturn($product);
        $values->toArray()->willReturn(['foo' => 'bar', 'bar' => 'baz']);
        $product->getIdentifier()->willReturn('test');
        $product->getValues()->willReturn($values);
        $product->getFamily()->willReturn($family);
        $suggestedData = new ProposalSuggestedData(343, ['foo' => 'bar']);

        $productRepository->find(1556)->willReturn($product2);
        $values2->toArray()->willReturn(['test' => 0]);
        $product2->getIdentifier()->willReturn('test2');
        $product2->getValues()->willReturn($values2);
        $product2->getFamily()->willReturn($family);
        $suggestedData2 = new ProposalSuggestedData(1556, ['test' => 42]);

        $productUpdater->update($product, ['values' => ['foo' => 'bar']])->shouldBeCalled();
        $productUpdater->update($product2, ['values' => ['test' => 42]])->shouldBeCalled();

        $this->process([$suggestedData, $suggestedData2], 'an_author')->shouldReturn(null);

        $this->hasProposalForProduct('test', 'an_author')->shouldReturn(true);
        $this->hasProposalForProduct('test2', 'an_author')->shouldReturn(true);
    }
}
