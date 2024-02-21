<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Webhook\Exception;

use Akeneo\Connectivity\Connection\Domain\Webhook\Exception\ConnectionWebhookNotFoundException;
use PhpSpec\ObjectBehavior;

class ConnectionWebhookNotFoundExceptionSpec extends ObjectBehavior
{
    public function it_is_a_connection_webhook_not_found_exception(): void
    {
        $this->shouldHaveType(ConnectionWebhookNotFoundException::class);
        $this->shouldBeAnInstanceOf(\DomainException::class);
    }

    public function it_provides_a_default_error_message(): void
    {
        $this->getMessage()->shouldReturn('akeneo_connectivity.connection.webhook.error.not_found');
    }
}
