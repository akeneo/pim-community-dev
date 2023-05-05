<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use PhpSpec\ObjectBehavior;

class DeleteAppCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('test');
    }

    public function it_is_a_delete_app_command(): void
    {
        $this->shouldHaveType(DeleteAppCommand::class);
    }

    public function it_gets_app_id(): void
    {
        $this->getAppId()->shouldReturn('test');
    }
}
