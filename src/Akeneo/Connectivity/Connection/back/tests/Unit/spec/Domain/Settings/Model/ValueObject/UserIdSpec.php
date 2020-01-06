<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use PhpSpec\ObjectBehavior;

class UserIdSpec extends ObjectBehavior
{
    public function it_is_a_user_id(): void
    {
        $this->beConstructedWith(42);
        $this->shouldHaveType(UserId::class);
    }

    public function it_provides_a_user_id()
    {
        $this->beConstructedWith(42);
        $this->id()->shouldReturn(42);
    }

    public function it_validates_itself()
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException('User id must be positive.')
            )
            ->during('__construct', [-2]);
    }
}
