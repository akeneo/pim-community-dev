<?php

namespace spec\Pim\Bundle\ApiBundle\Negotiator;

use FOS\RestBundle\Util\StopFormatListenerException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ApiBundle\Negotiator\ContentTypeNegotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class ContentTypeNegotiatorSpec extends ObjectBehavior
{
    public function let(RequestMatcherInterface $requestMatcher1, RequestMatcherInterface $requestMatcher2)
    {
        $this->add($requestMatcher1, ['content_types' => ['application/json']]);
        $this->add($requestMatcher2, ['stop' => true]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentTypeNegotiator::class);
    }

    public function it_returns_content_types_for_a_matching_request($requestMatcher1, Request $request)
    {
        $requestMatcher1->matches($request)->willReturn(true);

        $this->getContentTypes($request)->shouldReturn(['application/json']);
    }

    public function it_throws_stop_format_exception_when_matching_request_with_stop_rule(
        $requestMatcher1,
        $requestMatcher2,
        Request $request
    ) {
        $requestMatcher1->matches($request)->willReturn(false);
        $requestMatcher2->matches($request)->willReturn(true);

        $this
            ->shouldThrow(new StopFormatListenerException('Stopped content type negotiator'))
            ->during('getContentTypes', [$request]);
    }
}
