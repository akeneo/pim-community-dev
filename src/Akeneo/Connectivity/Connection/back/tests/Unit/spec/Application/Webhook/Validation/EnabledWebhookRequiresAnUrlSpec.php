<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\EnabledWebhookRequiresAnUrl;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class EnabledWebhookRequiresAnUrlSpec extends ObjectBehavior
{
    public function it_is_an_enabled_webhook_requires_an_url_constraint(): void
    {
        $this->shouldHaveType(EnabledWebhookRequiresAnUrl::class);
        $this->shouldBeAnInstanceOf(Constraint::class);
    }

    public function it_provides_targets(): void
    {
        $this->getTargets()->shouldReturn(EnabledWebhookRequiresAnUrl::CLASS_CONSTRAINT);
    }

    public function it_provides_a_message(): void
    {
        $this->message->shouldBe('akeneo_connectivity.connection.webhook.error.required');
    }
}
