<?php

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionResponse;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionResponseSpec extends ObjectBehavior
{
    function it_is_a_product_subscription_response()
    {
        $this->beConstructedWith('subscription-id', []);
        $this->shouldHaveType(ProductSubscriptionResponse::class);
    }

    function it_cannot_be_instantiated_with_an_empty_subscription_id()
    {
        $this->beConstructedWith('', []);
        $this->shouldThrow(new \InvalidArgumentException('subscription id cannot be empty'))->duringInstantiation();
    }

    function it_exposes_the_subscription_id()
    {
        $this->beConstructedWith('a-random-id', []);
        $this->getSubscriptionId()->shouldReturn('a-random-id');
    }

    function it_exposes_the_suggested_data()
    {
        $suggestedData = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
        $this->beConstructedWith('subscription-id', $suggestedData);
        $this->getSuggestedData()->shouldReturn($suggestedData);
    }
}
