<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsHandlerSpec extends ObjectBehavior
{
    public function let(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ): void {
        $this->beConstructedWith($suggestedDataNormalizer, $proposalUpsert, $subscriptionRepository);
    }

    public function it_is_a_create_proposal_handler(): void
    {
        $this->shouldHaveType(CreateProposalsHandler::class);
    }

    public function it_does_not_create_proposals_for_uncategorized_products(
        $proposalUpsert,
        $subscriptionRepository,
        ProductInterface $product,
        ProductSubscription $subscription
    ): void {
        $product->getCategoryCodes()->willReturn([]);
        $subscription->getProduct()->willReturn($product);
        $subscriptionRepository->findPendingSubscriptions()->willReturn([$subscription]);

        $proposalUpsert->process($product, Argument::type('array'), ProposalAuthor::USERNAME)->shouldNotBeCalled();

        $this->handle(new CreateProposalsCommand())->shouldReturn(null);
    }

    public function it_handles_a_create_proposal_command(
        $suggestedDataNormalizer,
        $proposalUpsert,
        $subscriptionRepository,
        ProductSubscription $subscription,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getCategoryCodes()->willReturn(['category_1']);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);
        $subscription->getProduct()->willReturn($product);
        $subscriptionRepository->findPendingSubscriptions()->willReturn([$subscription]);

        $suggestedData = new SuggestedData(
            [
                'foo' => 'Lorem ipsum dolor sit amet',
            ]
        );
        $normalizedData = [
            'foo' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ];

        $suggestedDataNormalizer->normalize($suggestedData)->willReturn($normalizedData);

        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getProduct()->willReturn($product);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);

        $proposalUpsert->process($product, $normalizedData, ProposalAuthor::USERNAME)->shouldBeCalled();

        $subscription->emptySuggestedData()->shouldBeCalled();
        $subscriptionRepository->save($subscription)->shouldBeCalled();

        $this->handle(new CreateProposalsCommand())->shouldReturn(null);
    }

    public function it_does_not_create_proposal_for_invalid_data(
        $suggestedDataNormalizer,
        $proposalUpsert,
        $subscriptionRepository,
        ProductSubscription $subscription,
        ProductInterface $product,
        FamilyInterface $family
    ): void {
        $product->getCategoryCodes()->willReturn(['category_1']);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);
        $subscription->getProduct()->willReturn($product);
        $subscriptionRepository->findPendingSubscriptions()->willReturn([$subscription]);

        $suggestedData = new SuggestedData(
            [
                'foo' => 'Lorem ipsum dolor sit amet',
            ]
        );
        $normalizedData = [
            'foo' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ];

        $suggestedDataNormalizer->normalize($suggestedData)->willReturn($normalizedData);

        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getProduct()->willReturn($product);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);

        $proposalUpsert->process($product, $normalizedData, ProposalAuthor::USERNAME)->willThrow(new \LogicException());

        $subscription->emptySuggestedData()->shouldNotBeCalled();
        $subscriptionRepository->save($subscription)->shouldNotBeCalled();

        $this->handle(new CreateProposalsCommand())->shouldReturn(null);
    }
}
