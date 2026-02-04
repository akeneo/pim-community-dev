<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler\BusinessEventHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service\ProcessFactoryInterface;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\BulkEventNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Process\Process;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BusinessEventHandlerSpec extends ObjectBehavior
{
    public function let(
        LoggerInterface $logger,
        BulkEventNormalizer $normalizer,
        ProcessFactoryInterface $processFactory,
        Process $process
    ): void {
        $processFactory->create(Argument::cetera())->willReturn($process);
        $process->setTimeout(null)->willReturn($process);
        $process->getCommandLine()->willReturn("'project_dir/bin/console' 'akeneo:connectivity:send-business-event' '[]'");
        $process->run(Argument::any())->willReturn(0);

        $this->beConstructedWith('project_dir', $logger, $normalizer, $processFactory);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(BusinessEventHandler::class);
        $this->shouldImplement(MessageSubscriberInterface::class);
    }

    public function it_handles_a_bulk_event_message(): void
    {
        $this->getHandledMessages()
            ->shouldYield(new \ArrayIterator([BulkEventInterface::class => ['from_transport' => 'webhook']]));
    }

    public function it_debugs_the_launched_command(
        LoggerInterface $logger,
        BulkEventNormalizer $normalizer,
        ProcessFactoryInterface $processFactory,
        Process $process
    ): void {
        $event = new BulkEvent([]);

        $normalizer->normalize($event)->willReturn([
            ['normalized_event1'],
            ['normalized_event2'],
        ]);

        $commandLine = "'project_dir/bin/console' 'akeneo:connectivity:send-business-event' '[[\"normalized_event1\"],[\"normalized_event2\"]]'";

        $processFactory->create(Argument::cetera())->willReturn($process);
        $process->setTimeout(null)->willReturn($process);
        $process->getCommandLine()->willReturn($commandLine);
        $process->run(Argument::any())->willReturn(0);

        $logger->debug(\sprintf('Command line: "%s"', $commandLine))->shouldBeCalled();

        $this->__invoke($event);
    }
}
