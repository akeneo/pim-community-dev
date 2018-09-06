<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandlerSpec extends ObjectBehavior
{
    function let(SuggestedDataNormalizer $suggestedDataNormalizer)
    {
        $this->beConstructedWith($suggestedDataNormalizer);
    }

    function it_is_a_create_proposal_handler()
    {
        $this->shouldHaveType(CreateProposalHandler::class);
    }

    function it_handles_a_create_proposal_command(
        $suggestedDataNormalizer,
        CreateProposalCommand $command,
        ProductSubscriptionInterface $subscription,
        ProductInterface $product,
        FamilyInterface $family
    ) {
        $suggestedData = new SuggestedData([
            'foo' => 'Lorem ipsum dolor sit amet',
        ]);
        $suggestedDataNormalizer->normalize($suggestedData)->willReturn([
            'foo' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'Lorem ipsum dolor sit amet',
                ],
            ],
        ]);

        $command->getProductSubscription()->willReturn($subscription);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getProduct()->willReturn($product);
        $product->getFamily()->willReturn($family);
        $family->getAttributeCodes()->willReturn(['foo']);

        $this->handle($command)->shouldReturn(null);
    }
}
