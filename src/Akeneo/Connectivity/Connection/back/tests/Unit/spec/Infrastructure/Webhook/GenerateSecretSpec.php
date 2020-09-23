<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\GenerateSecretInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\GenerateSecret;
use PhpSpec\ObjectBehavior;

class GenerateSecretSpec extends ObjectBehavior
{
    public function it_is_a_generate_secret_service(): void
    {
        $this->shouldHaveType(GenerateSecret::class);
        $this->shouldImplement(GenerateSecretInterface::class);
    }

    public function it_generates_a_secret(): void
    {
        $secret = $this->generate();
        $secret->shouldBeString();
    }
}
