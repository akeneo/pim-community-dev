<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;

class PublishedProductConsistencyExceptionSubscriberSpec extends ObjectBehavior
{
    function let(Router $router)
    {
        $this->beConstructedWith($router);
    }

    function it_subscribes_to_kernel_exception_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::EXCEPTION => 'onKernelException'
        ]);
    }

    function it_processes_only_published_product_consistency_exception(
        GetResponseForExceptionEvent $event,
        \Exception $exception
    ) {
        $event->getException()->willReturn($exception);
        $this->onKernelException($event)->shouldReturn(null);
    }

    function it_defines_a_redirect_response_if_needed(
        GetResponseForExceptionEvent $event,
        PublishedProductConsistencyException $consistencyException,
        $router,
        Request $request,
        Session $session,
        FlashBagInterface $flashBag
    ) {
        $event->getException()->willReturn($consistencyException);

        $consistencyException->getRoute()->willReturn('foo');
        $consistencyException->getRouteParams()->willReturn([]);

        // Mock redirect response
        $router->generate('foo', [])->willReturn('foobar');
        $response = new RedirectResponse('foobar');
        $event->setResponse($response)->shouldBeCalled();

        // Mock flash message is added
        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', Argument::any())->shouldBeCalled();

        $this->onKernelException($event)->shouldReturn(null);
    }
}
