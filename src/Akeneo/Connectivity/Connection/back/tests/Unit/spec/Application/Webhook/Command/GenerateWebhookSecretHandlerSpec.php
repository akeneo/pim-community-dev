<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretHandler;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\SaveWebhookSecretQueryInterface;
use PhpSpec\ObjectBehavior;

class GenerateWebhookSecretHandlerSpec extends ObjectBehavior
{
    public function let(GenerateSecretInterface $generateSecret, SaveWebhookSecretQueryInterface $saveQuery): void
    {
        $this->beConstructedWith($generateSecret, $saveQuery);
    }

    public function it_is_a_generate_webhook_secret_handler(): void
    {
        $this->shouldHaveType(GenerateWebhookSecretHandler::class);
    }

    public function it_generates_a_new_secret_for_a_connection($generateSecret, $saveQuery): void
    {
        $command = new GenerateWebhookSecretCommand('magento');

        $generateSecret->generate()->willReturn('1234_secret');
        $saveQuery->execute('magento', '1234_secret')->shouldBeCalled()->willReturn(true);

        $this->handle($command)->shouldReturn('1234_secret');
    }

    public function it_throws_an_exception_if_the_connection_does_not_exist($generateSecret, $saveQuery): void
    {
        $command = new GenerateWebhookSecretCommand('magento');

        $generateSecret->generate()->willReturn('1234_secret');
        $saveQuery->execute('magento', '1234_secret')->shouldBeCalled()->willReturn(false);

        $this
            ->shouldThrow(
                ConnectionWebhookNotFoundException::class
            )
            ->during('handle', [$command]);
    }
}
