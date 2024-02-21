<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppDeletion;
use PhpSpec\ObjectBehavior;

class AppDeletionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'app_id_123',
            'connection_code_123',
            'user_group_123',
            'ROLE_123',
        );
    }

    public function it_is_an_app_deletion(): void
    {
        $this->shouldHaveType(AppDeletion::class);
    }

    public function it_provides_an_app_id(): void
    {
        $this->getAppId()->shouldReturn('app_id_123');
    }

    public function it_provides_a_connection_code(): void
    {
        $this->getConnectionCode()->shouldReturn('connection_code_123');
    }

    public function it_provides_an_user_group(): void
    {
        $this->getUserGroupName()->shouldReturn('user_group_123');
    }

    public function it_provides_an_user_role(): void
    {
        $this->getUserRole()->shouldReturn('ROLE_123');
    }
}
