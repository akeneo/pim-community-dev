<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommandHandler;
use Akeneo\Connectivity\Connection\Application\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\CreateCustomAppQueryInterface;
use PhpSpec\ObjectBehavior;

class CreateCustomAppCommandHandlerSpec extends ObjectBehavior
{
    public function let(
        RandomCodeGeneratorInterface $randomCodeGenerator,
        CreateCustomAppQueryInterface $createCustomAppQuery,
    ): void {
        $this->beConstructedWith($randomCodeGenerator, $createCustomAppQuery);
    }

    public function it_is_a_create_custom_app_command_handler(): void
    {
        $this->shouldHaveType(CreateCustomAppCommandHandler::class);
    }

    public function it_creates_a_custom_app(
        RandomCodeGeneratorInterface $randomCodeGenerator,
        CreateCustomAppQueryInterface $createCustomAppQuery,
    ): void {
        $command = new CreateCustomAppCommand(
            'clientId1234',
            'Test app name',
            'http://activate-url.test',
            'http://callback-url.test',
            42,
        );

        $randomCodeGenerator->generate()->willReturn('abcd');
        $createCustomAppQuery->execute(
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
