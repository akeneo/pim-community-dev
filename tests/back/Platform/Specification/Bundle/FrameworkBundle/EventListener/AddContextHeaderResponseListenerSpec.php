<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Akeneo\Connectivity\Connection\Application\Apps\Security\FindCurrentAppIdInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Logging\BoundedContextResolver;
use Akeneo\Platform\Bundle\FrameworkBundle\EventListener\AddContextHeaderResponseListener;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AddContextHeaderResponseListenerSpec extends ObjectBehavior
{
    function it_injects_response_headers_without_query_string(
        BoundedContextResolver $boundedContextResolver,
        HttpKernelInterface $kernel,
        Request $request,
        Response $response,
        ResponseHeaderBag $headers,
        ParameterBag $requestAttributes,
        FindCurrentAppIdInterface $findCurrentAppId,
    ) {
        $boundedContextResolver->fromRequest($request)->shouldBeCalled()->willReturn('my_context');
        $findCurrentAppId->execute()->shouldBeCalled()->willReturn('my_app_id');

        $event = new ResponseEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $response->getWrappedObject()
        );

        $request->getPathInfo()->willReturn('my_path_info');
        $requestAttributes->get('_route', 'undefined')->willReturn('my_symfony_route');
        $request->attributes = $requestAttributes;

        $response->headers = $headers;

        $this->beConstructedWith($boundedContextResolver, $findCurrentAppId, 'my_tenant_id');
        $this->shouldHaveType(AddContextHeaderResponseListener::class);

        $headers->set('x-akeneo-context', 'my_context')->shouldBeCalled();
        $headers->set('x-request-path', 'my_path_info')->shouldBeCalled();
        $headers->set('x-symfony-route', 'my_symfony_route')->shouldBeCalled();
        $headers->set('x-app-id', 'my_app_id')->shouldBeCalled();
        $headers->set('x-app-tenant-id', 'my_tenant_id')->shouldBeCalled();

        $this->injectAkeneoContextHeader($event);
    }
}
