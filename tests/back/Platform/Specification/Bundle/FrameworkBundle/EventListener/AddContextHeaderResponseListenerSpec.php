<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Akeneo\Platform\Bundle\FrameworkBundle\BoundedContext\BoundedContextResolver;
use Akeneo\Platform\Bundle\FrameworkBundle\EventListener\AddContextHeaderResponseListener;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AddContextHeaderResponseListenerSpec extends ObjectBehavior
{
    function it_injects_response_headers_without_query_string(
        BoundedContextResolver $boundedContextResolver,
        Request $request,
        ResponseEvent $event,
        Response $response,
        ResponseHeaderBag $headers
    ) {
        $boundedContextResolver->fromRequest($request)->shouldBeCalled()->willReturn('my_context');

        $event->getRequest()->shouldBeCalled()->willReturn($request);
        $request->getPathInfo()->willReturn('my_path_info');

        $event->getResponse()->shouldBeCalled()->willReturn($response);

        $response->headers = $headers;

        $this->beConstructedWith($boundedContextResolver);
        $this->shouldHaveType(AddContextHeaderResponseListener::class);

        $headers->set('x-akeneo-context', 'my_context')->shouldBeCalled();
        $headers->set('x-request-path', 'my_path_info')->shouldBeCalled();

        $this->injectAkeneoContextHeader($event);
    }
}
