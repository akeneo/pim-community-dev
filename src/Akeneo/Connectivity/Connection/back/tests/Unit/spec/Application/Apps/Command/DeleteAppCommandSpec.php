<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use PhpSpec\ObjectBehavior;

class DeleteAppCommandSpec extends ObjectBehavior
{
    public function it_is_a_delete_app_handler(): void
    {
        $this->shouldHaveType(DeleteAppCommand::class);
    }
}
