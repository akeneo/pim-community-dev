<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory\SuggestedDataFactory;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsHandlerSpec extends ObjectBehavior
{
    public function let(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SuggestedDataFactory $suggestedDataFactory
    ): void {
        $this->beConstructedWith($proposalUpsert, $subscriptionRepository, $suggestedDataFactory, 2);
    }

    public function it_is_a_create_proposal_handler(): void
    {
        $this->shouldHaveType(CreateProposalsHandler::class);
    }

    public function it_throws_an_exception_if_batch_size_is_zero(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SuggestedDataFactory $suggestedDataFactory
    ): void {
        $this->beConstructedWith($proposalUpsert, $subscriptionRepository, $suggestedDataFactory, 0);
        $this->shouldThrow(new \InvalidArgumentException('Batch size must be positive'))->duringInstantiation();
    }

    public function it_throws_an_exception_if_batch_size_is_negative(
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SuggestedDataFactory $suggestedDataFactory
    ): void {
        $this->beConstructedWith($proposalUpsert, $subscriptionRepository, $suggestedDataFactory, -5);
        $this->shouldThrow(new \InvalidArgumentException('Batch size must be positive'))->duringInstantiation();
    }

    public function it_does_not_process_invalid_subscriptions(
        $proposalUpsert,
        $subscriptionRepository,
        $suggestedDataFactory,
        ProductSubscription $subscription
    ): void {
        $subscription->getSubscriptionId()->willReturn('abc-123');
        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription]);
        $subscriptionRepository->findPendingSubscriptions(2, 'abc-123')->willReturn([]);
        $suggestedDataFactory->fromSubscription($subscription)->willReturn(null);

        $proposalUpsert->process(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->handle(new CreateProposalsCommand());
    }

    public function it_paginates_proposals_creation(
        $proposalUpsert,
        $subscriptionRepository,
        $suggestedDataFactory,
        ProductSubscription $subscription1,
        ProductSubscription $subscription2,
        ProductSubscription $subscription3
    ): void {
        $subscription1->getSubscriptionId()->shouldNotBeCalled();
        $suggestedDataFactory->fromSubscription($subscription1)->willReturn(
            new SuggestedData(['foo' => 'bar'], new Product())
        );
        $subscription2->getSubscriptionId()->willReturn('abc');
        $suggestedDataFactory->fromSubscription($subscription2)->willReturn(
            new SuggestedData(['bar' => 'baz'], new Product())
        );
        $subscription3->getSubscriptionId()->willReturn('def');
        $suggestedDataFactory->fromSubscription($subscription3)->willReturn(
            new SuggestedData(['test' => 42], new Product())
        );

        $subscriptionRepository->findPendingSubscriptions(2, null)->willReturn([$subscription1, $subscription2]);
        $subscriptionRepository->findPendingSubscriptions(2, 'abc')->willReturn([$subscription3]);
        $subscriptionRepository->findPendingSubscriptions(2, 'def')->willReturn([]);

        $proposalUpsert->process(Argument::type('array'), ProposalAuthor::USERNAME)->shouldBeCalledTimes(2);

        $this->handle(new CreateProposalsCommand());
    }
}
