<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommandHandler;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\CreateTestAppQueryInterface;
use PhpSpec\ObjectBehavior;

class CreateTestAppCommandHandlerSpec extends ObjectBehavior
{
    public function let(
        RandomCodeGeneratorInterface $randomCodeGenerator,
        CreateTestAppQueryInterface $createTestAppQuery,
    ): void {
        $this->beConstructedWith($randomCodeGenerator, $createTestAppQuery);
    }

    public function it_is_a_create_test_app_command_handler(): void
    {
        $this->shouldHaveType(CreateTestAppCommandHandler::class);
    }

    public function it_creates_a_test_app(
        RandomCodeGeneratorInterface $randomCodeGenerator,
        CreateTestAppQueryInterface $createTestAppQuery,
    ): void {
        $command = new CreateTestAppCommand(
            'clientId1234',
            'Test app name',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        );

        $randomCodeGenerator->generate()->willReturn('abcd');
        $createTestAppQuery->execute(
            'clientId1234',
            'Test app name',
            'http://activate-url.test',
            'http://callback-url.test',
            'abcd',
            42
        )->shouldBeCalled();

        $this->handle($command);
    }
}
