<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Middleware\HandleProcessMessageMiddleware;
use Akeneo\Tool\Bundle\MessengerBundle\Process\RunMessageProcess;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleProcessMessageMiddlewareSpec extends ObjectBehavior
{
    public function let(RunMessageProcess $runUcsMessageProcess): void
    {
        $this->beConstructedWith($runUcsMessageProcess);
    }

    public function it_is_a_middleware(): void
    {
        $this->shouldImplement(MiddlewareInterface::class);
        $this->shouldHaveType(HandleProcessMessageMiddleware::class);
    }

    public function it_handles_an_envelope_with_a_tenant_id(
        RunMessageProcess $runUcsMessageProcess,
        StackInterface $stack,
        MiddlewareInterface $stackMiddleware,
    ): void {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            new TenantIdStamp('pim-test'),
            new ReceivedStamp('consumer1'),
            new CorrelationIdStamp('123456'),
        ]);

        $runUcsMessageProcess->__invoke($message, 'consumer1', 'pim-test', '123456')->shouldBeCalledOnce();
        $stack->next()->willReturn($stackMiddleware);
        $stackMiddleware->handle($envelope, $stack)->willReturn($envelope);

        $this->handle($envelope, $stack);
    }

    public function it_handles_an_envelope_without_tenant_and_correlation_ids(
        RunMessageProcess $runUcsMessageProcess,
        StackInterface $stack,
        MiddlewareInterface $stackMiddleware,
    ): void {
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            new ReceivedStamp('consumer1'),
        ]);

        $runUcsMessageProcess->__invoke($message, 'consumer1', null, null)->shouldBeCalledOnce();
        $stack->next()->willReturn($stackMiddleware);
        $stackMiddleware->handle($envelope, $stack)->willReturn($envelope);

        $this->handle($envelope, $stack);
    }

    public function it_throws_an_exception_if_there_is_no_consumer_name(
        RunMessageProcess $runUcsMessageProcess,
        StackInterface $stack,
    ): void {
        $envelope = new Envelope(new \stdClass(), [
            new TenantIdStamp('pim-test'),
            new CorrelationIdStamp('123456'),
        ]);

        $runUcsMessageProcess->__invoke(Argument::cetera())->shouldNotBeCalled();
        $stack->next()->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->during('handle', [$envelope, $stack]);
    }
}
