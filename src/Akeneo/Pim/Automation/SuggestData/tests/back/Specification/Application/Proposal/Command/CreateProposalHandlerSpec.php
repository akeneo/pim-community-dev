<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\CreateProposalInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandlerSpec extends ObjectBehavior
{
    public function let(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        CreateProposalInterface $createProposal
    ) {
        $this->beConstructedWith($suggestedDataNormalizer, $createProposal);
    }

    public function it_is_a_create_proposal_handler()
    {
        $this->shouldHaveType(CreateProposalHandler::class);
    }

    public function it_does_not_do_anything_if_product_is_not_categorized(
        $suggestedDataNormalizer,
        CreateProposalCommand $command,
        ProductInterface $product,
        ProductSubscription $subscription
    ) {
        $product->getCategoryCodes()->willReturn([]);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

        $subscription->getSuggestedData()->shouldNotBeCalled();
        $suggestedDataNormalizer->normalize(Argument::any())->shouldNotBeCalled();

        $this->handle($command)->shouldReturn(null);
    }

    public function it_handles_a_create_proposal_command(
        $suggestedDataNormalizer,
        $createProposal,
        CreateProposalCommand $command,
        ProductSubscription $subscription,
        ProductInterface $product,
        FamilyInterface $family,
        EntityWithValuesDraftInterface $draft
    ) {
        $product->getCategoryCodes()->willReturn(['category_1']);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);
        $subscription->getProduct()->willReturn($product);
        $command->getProductSubscription()->willReturn($subscription);

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

        $createProposal->fromSuggestedData($product, $normalizedData, 'PIM.ai')->shouldBeCalled();

        $this->handle($command)->shouldReturn(null);
    }
}
