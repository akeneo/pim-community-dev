<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Model\ValueObject;

use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use PhpSpec\ObjectBehavior;

class ClientIdSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(42);
        $this->shouldBeAnInstanceOf(ClientId::class);
    }

    function it_must_be_positive()
    {
        $this->beConstructedWith(-1);
        $this->shouldThrow(
            new \InvalidArgumentException('Client id must be positive.')
        )->duringInstantiation();
    }

    function it_provides_the_client_id()
    {
        $this->beConstructedWith(42);
        $this->id()->shouldReturn(42);
    }
}
