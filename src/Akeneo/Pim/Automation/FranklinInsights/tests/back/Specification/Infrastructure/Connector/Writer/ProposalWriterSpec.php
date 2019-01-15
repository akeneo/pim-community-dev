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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer\ProposalWriter;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalWriterSpec extends ObjectBehavior
{
    public function let(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        EntityManagerInterface $em,
        StepExecution $stepExecution
    ): void {
        $this->beConstructedWith($proposalUpsert, $subscriptionRepository, $em);
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
        $subscriptionRepository,
        $em
    ): void {
        $proposalSuggestedData1 = new ProposalSuggestedData(44, ['asin' => 'my-asin']);
        $proposalSuggestedData2 = new ProposalSuggestedData(31, ['upc' => 'my-upc']);

        $proposalUpsert
            ->process([$proposalSuggestedData1, $proposalSuggestedData2], ProposalAuthor::USERNAME)
            ->shouldBeCalled();
        $subscriptionRepository->emptySuggestedDataByProducts([44, 31])->shouldBeCalled();
        $em->clear()->shouldBeCalled();

        $this->write([$proposalSuggestedData1, $proposalSuggestedData2]);
    }

    public function it_increments_the_created_proposals_summary(
        $proposalUpsert,
        $subscriptionRepository,
        $em,
        $stepExecution
    ): void {
        $proposalSuggestedData1 = new ProposalSuggestedData(44, ['asin' => 'my-asin']);
        $proposalSuggestedData2 = new ProposalSuggestedData(31, ['upc' => 'my-upc']);

        $proposalUpsert
            ->process([$proposalSuggestedData1, $proposalSuggestedData2], ProposalAuthor::USERNAME)
            ->shouldBeCalled();
        $subscriptionRepository->emptySuggestedDataByProducts([44, 31])->shouldBeCalled();
        $em->clear()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('created', 2)->shouldBeCalled();

        $this->write([$proposalSuggestedData1, $proposalSuggestedData2]);
    }
}
