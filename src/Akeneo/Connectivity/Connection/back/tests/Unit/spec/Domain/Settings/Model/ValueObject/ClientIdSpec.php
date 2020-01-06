<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use PhpSpec\ObjectBehavior;

class ClientIdSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(42);
        $this->shouldBeAnInstanceOf(ClientId::class);
    }

    public function it_must_be_positive()
    {
        $this->beConstructedWith(-1);
        $this->shouldThrow(
            new \InvalidArgumentException('Client id must be positive.')
        )->duringInstantiation();
    }

    public function it_provides_the_client_id()
    {
        $this->beConstructedWith(42);
        $this->id()->shouldReturn(42);
    }
}
