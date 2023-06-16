<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\MessengerBundle\Command\ProcessMessageCommand;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Handler1ForMessage1;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\HandlerObserver;
use Akeneo\Tool\Bundle\MessengerBundle\tests\config\Message1;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProcessMessageCommandIntegration extends TestCase
{
    private Command $command;
    private HandlerObserver $handlerObserver;
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $application = new Application($this->testKernel);
        $this->command = $application->find(ProcessMessageCommand::getDefaultName());
        $this->handlerObserver = $this->get(HandlerObserver::class);
        $this->serializer = $this->get('akeneo_messenger.message.serializer');
    }

    public function test_it_processes_a_message(): void
    {
        Assert::assertSame(0, $this->handlerObserver->getTotalNumberOfExecution());

        $message = new Message1('test');

        $commandTester = new CommandTester($this->command);
        $statusCode = $commandTester->execute([
            'consumer_name' => 'consumer2',
            'message_class' => Message1::class,
            'message' => $this->serializer->serialize($message, 'json'),
        ]);

        Assert::assertSame(Command::SUCCESS, $statusCode);
        Assert::assertSame(1, $this->handlerObserver->getTotalNumberOfExecution());

        $commandTester = new CommandTester($this->command);
        $statusCode = $commandTester->execute([
            'consumer_name' => 'consumer1',
            'message_class' => Message1::class,
            'message' => $this->serializer->serialize($message, 'json'),
        ]);

        Assert::assertSame(Command::SUCCESS, $statusCode);
        Assert::assertTrue($this->handlerObserver->messageIsHandledByHandler($message, Handler1ForMessage1::class));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
