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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProposalWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->proposalUpsert = $proposalUpsert;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $proposalsSuggestedData): void
    {
        $productIds = [];
        foreach ($proposalsSuggestedData as $proposalSuggestedData) {
            $productIds[] = $proposalSuggestedData->getProductId();
        }

        $processedCount = $this->proposalUpsert->process($proposalsSuggestedData, ProposalAuthor::USERNAME);
        $this->subscriptionRepository->emptySuggestedDataByProducts($productIds);

        $this->stepExecution->incrementSummaryInfo('processed', $processedCount);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
