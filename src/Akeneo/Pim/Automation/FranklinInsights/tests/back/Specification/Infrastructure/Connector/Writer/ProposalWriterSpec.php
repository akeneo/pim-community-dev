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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer\ProposalWriter;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalWriterSpec extends ObjectBehavior
{
    public function let(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        StepExecution $stepExecution
    ): void {
        $this->beConstructedWith($proposalUpsert, $subscriptionRepository);
        $this->setStepExecution($stepExecution);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProposalWriter::class);
    }

    public function it_is_an_item_writer(): void
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_writes_proposals_and_empty_associated_suggested_data(
        $proposalUpsert,
        $subscriptionRepository
    ): void {
        $productId1 = new ProductId(44);
        $productId2 = new ProductId(31);
        $proposalSuggestedData1 = new ProposalSuggestedData($productId1, ['asin' => 'my-asin']);
        $proposalSuggestedData2 = new ProposalSuggestedData($productId2, ['upc' => 'my-upc']);

        $proposalUpsert
            ->process([$proposalSuggestedData1, $proposalSuggestedData2], ProposalAuthor::USERNAME)
            ->willReturn(2);
        $subscriptionRepository->emptySuggestedDataByProducts([$productId1, $productId2])->shouldBeCalled();

        $this->write([$proposalSuggestedData1, $proposalSuggestedData2]);
    }

    public function it_increments_the_created_proposals_summary(
        $proposalUpsert,
        $subscriptionRepository,
        $stepExecution
    ): void {
        $productId1 = new ProductId(44);
        $productId2 = new ProductId(31);
        $proposalSuggestedData1 = new ProposalSuggestedData($productId1, ['asin' => 'my-asin']);
        $proposalSuggestedData2 = new ProposalSuggestedData($productId2, ['upc' => 'my-upc']);

        $proposalUpsert
            ->process([$proposalSuggestedData1, $proposalSuggestedData2], ProposalAuthor::USERNAME)
            ->willReturn(2);
        $subscriptionRepository->emptySuggestedDataByProducts([$productId1, $productId2])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('processed', 2)->shouldBeCalled();

        $this->write([$proposalSuggestedData1, $proposalSuggestedData2]);
    }
}
