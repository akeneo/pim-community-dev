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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory\SuggestedDataFactory;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsHandler
{
    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var SuggestedDataFactory */
    private $suggestedDataFactory;

    /** @var ProductSubscription[] */
    private $pendingSubscriptions = [];

    /** @var string */
    private $searchAfter;

    /** @var int */
    private $batchSize;

    /**
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param SuggestedDataFactory $suggestedDataFactory
     */
    public function __construct(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        SuggestedDataFactory $suggestedDataFactory
    ) {
        $this->proposalUpsert = $proposalUpsert;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->suggestedDataFactory = $suggestedDataFactory;
    }

    /**
     * @param CreateProposalsCommand $command
     */
    public function handle(CreateProposalsCommand $command): void
    {
        $this->batchSize = $command->batchSize();
        $this->fetchNextPendingSubscriptions();

        while (!empty($this->pendingSubscriptions)) {
            $toProcess = [];
            foreach ($this->pendingSubscriptions as $subscription) {
                $data = $this->suggestedDataFactory->fromSubscription($subscription);
                if (null !== $data) {
                    $toProcess[] = $data;
                }
            }
            if (!empty($toProcess)) {
                $this->proposalUpsert->process($toProcess, ProposalAuthor::USERNAME);
            }
            $this->fetchNextPendingSubscriptions();
        }
    }

    /**
     * Fetches subscriptions with suggested data.
     */
    private function fetchNextPendingSubscriptions(): void
    {
        $pendingSubscriptions = $this->productSubscriptionRepository->findPendingSubscriptions(
            $this->batchSize,
            $this->searchAfter
        );
        if (!empty($pendingSubscriptions)) {
            $this->searchAfter = end($pendingSubscriptions)->getSubscriptionId();
            reset($pendingSubscriptions);
        }
        $this->pendingSubscriptions = $pendingSubscriptions;
    }
}
