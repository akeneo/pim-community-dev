<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\CollectApiError;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\MonitoredRoutes;
use Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber\ApiErrorEventSubscriber;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiErrorEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        RequestStack $requestStack,
        CollectApiError $collectApiError,
        Request $request,
        ExceptionEvent $exceptionEvent,
        TerminateEvent $terminateEvent
    ): void {
        $exceptionEvent->isMasterRequest()->willReturn(true);
        $exceptionEvent->getRequest()->willReturn($request);

        $terminateEvent->getRequest()->willReturn($request);

        $this->beConstructedWith($requestStack, $collectApiError);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([
            KernelEvents::EXCEPTION => 'collectApiError',
            KernelEvents::TERMINATE => 'saveApiErrors',
        ]);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(ApiErrorEventSubscriber::class);
        $this->shouldImplement(EventSubscriberInterface::class);
    }


    public function it_collects_errors_from_an_exception(
        $collectApiError,
        $request,
        $exceptionEvent
    ): void {
        $request->get('_route')->willReturn(MonitoredRoutes::ROUTES[0]);

        $exception = new HttpException(400);
        $exceptionEvent->getException()->willReturn($exception);

        $collectApiError->collectFromHttpException($exception)->shouldBeCalled();

        $this->collectApiError($exceptionEvent);
    }

    public function it_doesnt_collect_errors_from_an_exception_when_the_route_is_not_monitored(
        $collectApiError,
        $request,
        $exceptionEvent
    ): void {
        $request->get('_route')->willReturn('not_monitored_route');

        $collectApiError->collectFromHttpException()->shouldNotBeCalled();

        $this->collectApiError($exceptionEvent);
    }

    public function it_saves_collected_errors($collectApiError, $request, $terminateEvent): void
    {
        $request->get('_route')->willReturn(MonitoredRoutes::ROUTES[0]);

        $collectApiError->save()->shouldBeCalled();

        $this->saveApiErrors($terminateEvent);
    }

    public function it_doesnt_save_when_the_route_is_not_monitored($collectApiError, $request, $terminateEvent): void
    {
        $request->get('_route')->willReturn('not_monitored_route');

        $collectApiError->save()->shouldNotBeCalled();

        $this->saveApiErrors($terminateEvent);
    }
}
