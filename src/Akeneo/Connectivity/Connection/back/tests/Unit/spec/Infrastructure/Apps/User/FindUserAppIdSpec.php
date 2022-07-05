<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\User;

use Akeneo\Connectivity\Connection\Application\User\FindUserAppIdInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\FindUserAppId;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class FindUserAppIdSpec extends ObjectBehavior
{
    public function it_is_a_find_user_app_id(): void
    {
        $this->shouldHaveType(FindUserAppId::class);
        $this->shouldImplement(FindUserAppIdInterface::class);
    }

    public function it_finds_user_app_id(
        User $user,
    ): void {
        $user->getProperty('app_id')->willReturn('an_app_id');

        $this->execute($user)->shouldReturn('an_app_id');
    }

    public function it_returns_null_if_app_id_is_not_set(
        User $user,
    ): void {
        $user->getProperty('app_id')->willReturn(null);

        $this->execute($user)->shouldReturn(null);
    }
}
