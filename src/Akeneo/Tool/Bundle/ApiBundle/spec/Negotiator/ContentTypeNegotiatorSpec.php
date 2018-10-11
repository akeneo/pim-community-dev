<?php

namespace spec\Akeneo\Tool\Bundle\ApiBundle\Negotiator;

use FOS\RestBundle\Util\StopFormatListenerException;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class ContentTypeNegotiatorSpec extends ObjectBehavior
{
    function let(
        RequestMatcherInterface $requestMatcher1,
        RequestMatcherInterface $requestMatcher2,
        RequestMatcherInterface $requestMatcher3
    ) {
        $this->add($requestMatcher2, ['content_types' => ['application/json'], 'priority' => 10]);
        $this->add($requestMatcher1, ['content_types' => ['application/json'], 'priority' => 1]);
        $this->add($requestMatcher3, ['stop' => true, 'priority' => 100]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentTypeNegotiator::class);
    }

    public function it_returns_content_types_for_a_matching_request_by_order_of_priority($requestMatcher1, $requestMatcher2, Request $request)
    {
        $requestMatcher1->matches($request)->shouldBeCalled()->willReturn(false);
        $requestMatcher2->matches($request)->shouldBeCalled()->willReturn(true);

        $this->getContentTypes($request)->shouldReturn(['application/json']);
    }

    public function it_throws_stop_format_exception_when_matching_request_with_stop_rule(
        $requestMatcher1,
        $requestMatcher2,
        $requestMatcher3,
        Request $request
    ) {
        $requestMatcher1->matches($request)->willReturn(false);
        $requestMatcher2->matches($request)->willReturn(false);
        $requestMatcher3->matches($request)->willReturn(true);

        $this
            ->shouldThrow(new StopFormatListenerException('Stopped content type negotiator'))
            ->during('getContentTypes', [$request]);
    }
}
