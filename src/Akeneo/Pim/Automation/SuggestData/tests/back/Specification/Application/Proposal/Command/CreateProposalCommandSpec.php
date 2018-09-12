<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalCommandSpec extends ObjectBehavior
{
    public function let(ProductSubscription $subscription)
    {
        $suggestedData = new SuggestedData(['foo' => 'bar']);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $this->beConstructedWith($subscription);
    }

    public function it_is_a_create_proposal_command()
    {
        $this->shouldBeAnInstanceOf(CreateProposalCommand::class);
    }

    public function it_exposes_the_product_subscription($subscription)
    {
        $this->getProductSubscription()->shouldReturn($subscription);
    }

    public function it_throws_an_exception_if_there_is_no_suggested_data($subscription)
    {
        $suggestedData = new SuggestedData([]);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getSubscriptionId()->willReturn('fake-subscription-id');
        $this->shouldThrow(
            new \InvalidArgumentException('There is no suggested data for subscription fake-subscription-id')
        )->duringInstantiation();
    }
}
