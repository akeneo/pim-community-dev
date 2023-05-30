<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Handler\UcsEnvelopeMessageHandler;
use Akeneo\Tool\Bundle\MessengerBundle\Middleware\HandleUcsMessageMiddleware;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleUcsMessageMiddlewareSpec extends ObjectBehavior
{
    public function let(UcsEnvelopeMessageHandler $handler): void
    {
        $this->beConstructedWith($handler);
    }

    public function it_is_a_middleware(): void
    {
        $this->shouldImplement(MiddlewareInterface::class);
        $this->shouldHaveType(HandleUcsMessageMiddleware::class);
    }

    public function it_handles_an_envelope_and_stops_the_middleware_chain(
        UcsEnvelopeMessageHandler $handler,
        StackInterface $stack,
    ): void {
        $envelope = new Envelope(new \stdClass(), []);

        $handler->__invoke($envelope)->shouldBeCalledOnce();
        $stack->next()->shouldNotBeCalled();

        $this->handle($envelope, $stack)->shouldReturn($envelope);
    }
}
