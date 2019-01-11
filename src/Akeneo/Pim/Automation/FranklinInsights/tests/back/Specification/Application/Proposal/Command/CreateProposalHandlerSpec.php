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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalAuthor;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class CreateProposalHandlerSpec extends ObjectBehavior
{
    public function let(
        ProposalSuggestedDataFactory $proposalSuggestedDataFactory,
        ProposalUpsertInterface $proposalUpsert
    ): void {
        $this->beConstructedWith(
            $proposalSuggestedDataFactory,
            $proposalUpsert
        );
    }

    public function it_is_a_create_proposal_handler(): void
    {
        $this->shouldBeAnInstanceOf(CreateProposalHandler::class);
    }

    public function it_handles_a_proposal_creation(
        $proposalSuggestedDataFactory,
        $proposalUpsert
    ): void {
        $productSubscription = new ProductSubscription(42, uniqid(), []);

        $suggestedData = new ProposalSuggestedData(42, []);
        $proposalSuggestedDataFactory->fromSubscription($productSubscription)->willReturn($suggestedData);

        $proposalUpsert->process([$suggestedData], ProposalAuthor::USERNAME)->shouldBeCalled();

        $this->handle(new CreateProposalCommand($productSubscription));
    }

    public function it_does_not_create_a_proposal_if_there_is_no_suggested_data(
        $proposalSuggestedDataFactory,
        $proposalUpsert
    ): void {
        $productSubscription = new ProductSubscription(42, uniqid(), []);

        $proposalSuggestedDataFactory->fromSubscription($productSubscription)->willReturn(null);

        $proposalUpsert->process(Argument::cetera())->shouldNotBeCalled();

        $this->handle(new CreateProposalCommand($productSubscription));
    }
}
