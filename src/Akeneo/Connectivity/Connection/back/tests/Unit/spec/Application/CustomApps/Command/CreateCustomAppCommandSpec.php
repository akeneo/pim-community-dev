<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\CustomApps\Command;

use Akeneo\Connectivity\Connection\Application\CustomApps\Command\CreateCustomAppCommand;
use PhpSpec\ObjectBehavior;

class CreateCustomAppCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'UUID1234',
            'name of the app',
            'http://activate_url.test',
            'http://callback_url.test',
            42,
        );
    }

    public function it_is_a_create_custom_app_command(): void
    {
        $this->shouldHaveType(CreateCustomAppCommand::class);
    }

    public function it_provides_a_client_id(): void
    {
        $this->clientId->shouldReturn('UUID1234');
    }

    public function it_provides_a_name(): void
    {
        $this->name->shouldReturn('name of the app');
    }

    public function it_provides_an_activate_url(): void
    {
        $this->activateUrl->shouldReturn('http://activate_url.test');
    }

    public function it_provides_a_callback_url(): void
    {
        $this->callbackUrl->shouldReturn('http://callback_url.test');
    }

    public function it_provides_a_user_id(): void
    {
        $this->userId->shouldReturn(42);
    }
}
