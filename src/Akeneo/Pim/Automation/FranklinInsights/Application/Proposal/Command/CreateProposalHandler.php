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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateProposalHandler
{
    /** @var ProposalSuggestedDataFactory */
    private $proposalSuggestedDataFactory;

    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param ProposalSuggestedDataFactory $proposalSuggestedDataFactory
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        ProposalSuggestedDataFactory $proposalSuggestedDataFactory,
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->proposalSuggestedDataFactory = $proposalSuggestedDataFactory;
        $this->proposalUpsert = $proposalUpsert;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param CreateProposalCommand $command
     */
    public function handle(CreateProposalCommand $command): void
    {
        $proposalSuggestedData = $this->proposalSuggestedDataFactory->fromSubscription(
            $command->getProductSubscription()
        );

        if ($proposalSuggestedData instanceof ProposalSuggestedData) {
            $this->proposalUpsert->process([$proposalSuggestedData], ProposalAuthor::USERNAME);
            $this->subscriptionRepository->emptySuggestedDataByProducts(
                [$command->getProductSubscription()->getProductId()]
            );
        }
    }
}
