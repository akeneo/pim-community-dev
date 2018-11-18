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

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsHandler
{
    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /** @var ProposalSuggestedDataFactory */
    private $suggestedDataFactory;

    /** @var int */
    private $batchSize;

    /** @var ProductSubscription[] */
    private $pendingSubscriptions = [];

    /** @var string */
    private $searchAfter;

    /**
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     * @param ProposalSuggestedDataFactory $suggestedDataFactory
     * @param int $batchSize
     */
    public function __construct(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository,
        ProposalSuggestedDataFactory $suggestedDataFactory,
        int $batchSize
    ) {
        if ($batchSize <= 0) {
            throw new \InvalidArgumentException('Batch size must be positive');
        }

        $this->proposalUpsert = $proposalUpsert;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->suggestedDataFactory = $suggestedDataFactory;
        $this->batchSize = $batchSize;
    }

    /**
     * @param CreateProposalsCommand $command
     */
    public function handle(CreateProposalsCommand $command): void
    {
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
     * Fetches the next $batchSize subscriptions which have suggested data.
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
