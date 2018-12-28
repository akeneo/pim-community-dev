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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\InMemoryProposalUpsert;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalContext implements Context
{
    /** @var CreateProposalsHandler */
    private $createProposalsHandler;

    /** @var InMemoryProposalUpsert */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param CreateProposalsHandler $createProposalsHandler
     * @param InMemoryProposalUpsert $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        CreateProposalsHandler $createProposalsHandler,
        InMemoryProposalUpsert $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->createProposalsHandler = $createProposalsHandler;
        $this->proposalUpsert = $proposalUpsert;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @When the system creates proposals for suggested data
     */
    public function theSystemCreatesProposalsForSuggestedData(): void
    {
        $this->createProposalsHandler->handle(new CreateProposalsCommand());
    }

    /**
     * @Then there should be a proposal for product :identifier
     *
     * @param string $identifier
     */
    public function thereShouldBeAProposalForProduct(string $identifier): void
    {
        Assert::true($this->proposalUpsert->hasProposalForProduct($identifier, ProposalAuthor::USERNAME));
        Assert::isEmpty($this->subscriptionRepository->findPendingSubscriptions(10, null));
    }

    /**
     * @Then there should not be a proposal for product :identifier
     *
     * @param string $identifier
     */
    public function thereShouldNotBeAnyProposalForProduct(string $identifier): void
    {
        Assert::false($this->proposalUpsert->hasProposalForProduct($identifier, ProposalAuthor::USERNAME));
    }

    /**
     * @Then there should not have any proposal
     */
    public function thereShouldNotHaveAnyProposal(): void
    {
        Assert::false($this->proposalUpsert->hasProposal());
    }
}
