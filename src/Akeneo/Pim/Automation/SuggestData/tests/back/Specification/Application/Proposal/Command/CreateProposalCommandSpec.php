<?php

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalCommandSpec extends ObjectBehavior
{
    function let(ProductSubscriptionInterface $subscription)
    {
        $suggestedData = new SuggestedData(['foo' => 'bar']);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $this->beConstructedWith($subscription);
    }

    function it_is_a_create_proposal_command()
    {
        $this->shouldBeAnInstanceOf(CreateProposalCommand::class);
    }

    function it_exposes_the_product_subscription($subscription)
    {
        $this->getProductSubscription()->shouldReturn($subscription);
    }

    function it_throws_an_exception_if_there_is_no_suggested_data($subscription)
    {
        $suggestedData = new SuggestedData([]);
        $subscription->getSuggestedData()->willReturn($suggestedData);
        $subscription->getSubscriptionId()->willReturn('fake-subscription-id');
        $this->shouldThrow(
            new \InvalidArgumentException('There is no suggested data for subscription fake-subscription-id')
        )->duringInstantiation();
    }
}
