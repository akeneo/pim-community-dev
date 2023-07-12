<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Middleware;

use Akeneo\Tool\Bundle\MessengerBundle\Middleware\HandleProcessMessageMiddleware;
use Akeneo\Tool\Bundle\MessengerBundle\Process\RunMessageProcess;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\CorrelationIdStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ReceiverStamp;
use Akeneo\Tool\Bundle\MessengerBundle\Stamp\TenantIdStamp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleProcessMessageMiddlewareSpec extends ObjectBehavior
{
    public function let(RunMessageProcess $runUcsMessageProcess, LoggerInterface $logger): void
    {
        $this->beConstructedWith($runUcsMessageProcess, $logger);
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
        $receiver = new InMemoryTransport();
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            new TenantIdStamp('pim-test'),
            new ReceivedStamp('consumer1'),
            new ReceiverStamp($receiver),
            new CorrelationIdStamp('123456'),
        ]);

        $runUcsMessageProcess->__invoke($envelope, 'consumer1', $receiver, 'pim-test', '123456')->shouldBeCalledOnce();
        $stack->next()->willReturn($stackMiddleware);
        $stackMiddleware->handle($envelope, $stack)->willReturn($envelope);

        $this->handle($envelope, $stack);
    }

    public function it_handles_an_envelope_without_tenant_and_correlation_ids(
        RunMessageProcess $runUcsMessageProcess,
        StackInterface $stack,
        MiddlewareInterface $stackMiddleware,
    ): void {
        $receiver = new InMemoryTransport();
        $message = new \stdClass();
        $envelope = new Envelope($message, [
            new ReceivedStamp('consumer1'),
            new ReceiverStamp($receiver),
        ]);

        $runUcsMessageProcess->__invoke($envelope, 'consumer1', $receiver, null, null)->shouldBeCalledOnce();
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

    public function it_throws_an_exception_if_there_is_no_receiver(
        RunMessageProcess $runUcsMessageProcess,
        StackInterface $stack,
    ): void {
        $envelope = new Envelope(new \stdClass(), [
            new TenantIdStamp('pim-test'),
            new CorrelationIdStamp('123456'),
            new ReceivedStamp('consumer1'),
        ]);

        $runUcsMessageProcess->__invoke(Argument::cetera())->shouldNotBeCalled();
        $stack->next()->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)->during('handle', [$envelope, $stack]);
    }
}
