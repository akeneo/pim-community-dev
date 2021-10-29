<?php

namespace Specification\Akeneo\Platform\Bundle\FrameworkBundle\EventListener;

use Akeneo\Platform\Bundle\FrameworkBundle\EventListener\ErrorListener;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ErrorListenerSpec extends ObjectBehavior
{
    function it_logs_enriched_http_exception(
        LoggerInterface $logger,
        HttpKernelInterface $kernel,
        Request $request
    ) {
        $httpException = new HttpException(Response::HTTP_BAD_REQUEST, 'my error message');
        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $httpException
        );

        $this->beConstructedWith(
            $controller = new \stdClass(),
            $logger
        );
        $this->shouldHaveType(ErrorListener::class);

        $logger->notice(
            Argument::containingString(
                'Uncaught PHP Exception Symfony\Component\HttpKernel\Exception\HttpException: "my error message" at /srv/pim/tests/back/Platform/Specification/Bundle/FrameworkBundle/EventListener/ErrorListenerSpec.php line'
            ),
            Argument::allOf(
                Argument::withEntry('exception', $httpException),
                Argument::withKey('trace')
            )
        )->shouldBeCalled();

        $this->logKernelException($event);
    }

    function it_logs_enriched_exception(
        LoggerInterface $logger,
        HttpKernelInterface $kernel,
        Request $request
    ) {
        $httpException = new \Exception('my error message');
        $event = new ExceptionEvent(
            $kernel->getWrappedObject(),
            $request->getWrappedObject(),
            HttpKernelInterface::MAIN_REQUEST,
            $httpException
        );

        $this->beConstructedWith(
            $controller = new \stdClass(),
            $logger
        );
        $this->shouldHaveType(ErrorListener::class);

        $logger->critical(
            Argument::containingString(
                'Uncaught PHP Exception Exception: "my error message" at /srv/pim/tests/back/Platform/Specification/Bundle/FrameworkBundle/EventListener/ErrorListenerSpec'
            ),
            Argument::allOf(
                Argument::withEntry('exception', $httpException),
                Argument::withKey('trace')
            )
        )->shouldBeCalled();

        $this->logKernelException($event);
    }
}
