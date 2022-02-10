<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command\CreateTestAppCommand;
use PhpSpec\ObjectBehavior;

class CreateTestAppCommandSpec extends ObjectBehavior
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

    public function it_is_a_create_test_app_command(): void
    {
        $this->shouldHaveType(CreateTestAppCommand::class);
    }

    public function it_provides_a_client_id(): void
    {
        $this->getClientId()->shouldReturn('UUID1234');
    }

    public function it_provides_a_name(): void
    {
        $this->getName()->shouldReturn('name of the app');
    }

    public function it_provides_an_activate_url(): void
    {
        $this->getActivateUrl()->shouldReturn('http://activate_url.test');
    }

    public function it_provides_a_callback_url(): void
    {
        $this->getCallbackUrl()->shouldReturn('http://callback_url.test');
    }

    public function it_provides_a_user_id(): void
    {
        $this->getUserId()->shouldReturn(42);
    }
}
