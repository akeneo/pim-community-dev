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
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProposalWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var EntityManagerInterface */
    private $em;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        EntityManagerInterface $em
    ) {
        $this->proposalUpsert = $proposalUpsert;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->em = $em;
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

        $this->proposalUpsert->process($proposalsSuggestedData, ProposalAuthor::USERNAME);
        $this->subscriptionRepository->emptySuggestedDataByProducts($productIds);
        $this->em->clear();

        $this->stepExecution->incrementSummaryInfo('created', count($productIds));
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
