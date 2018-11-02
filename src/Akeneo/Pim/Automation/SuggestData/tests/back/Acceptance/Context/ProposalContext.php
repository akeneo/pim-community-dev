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

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Proposal\InMemoryProposalUpsert;
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

    /**
     * @param CreateProposalsHandler $createProposalsHandler
     * @param InMemoryProposalUpsert $proposalUpsert
     */
    public function __construct(
        CreateProposalsHandler $createProposalsHandler,
        InMemoryProposalUpsert $proposalUpsert
    ) {
        $this->createProposalsHandler = $createProposalsHandler;
        $this->proposalUpsert = $proposalUpsert;
    }

    /**
     * @When the system creates proposals for suggested data
     */
    public function theSystemCreatesProposalsForSuggestedData(): void
    {
        $this->createProposalsHandler->handle(new CreateProposalsCommand(100));
    }

    /**
     * @Then there should be a proposal for product :identifier
     *
     * @param string îdentifier
     */
    public function thereShouldBeOneProposalForProduct(string $identifier): void
    {
        Assert::true($this->proposalUpsert->hasProposalForProduct($identifier, ProposalAuthor::USERNAME));
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
}
