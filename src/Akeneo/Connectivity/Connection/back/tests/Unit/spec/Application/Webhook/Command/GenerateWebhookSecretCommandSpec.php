<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Command;

use Akeneo\Connectivity\Connection\Application\Webhook\Command\GenerateWebhookSecretCommand;
use PhpSpec\ObjectBehavior;

class GenerateWebhookSecretCommandSpec extends ObjectBehavior
{
    public function it_is_a_generate_webhook_secret_command(): void
    {
        $this->beConstructedWith('magento');
        $this->shouldHaveType(GenerateWebhookSecretCommand::class);
    }

    public function it_provides_a_connection_code(): void
    {
        $this->beConstructedWith('magento');
        $this->connectionCode()->shouldReturn('magento');
    }
}
