<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use PhpSpec\ObjectBehavior;

class WrongCredentialsCombinationSpec extends ObjectBehavior
{
    public function it_is_a_wrong_credentials_combination(): void
    {
        $this->shouldHaveType(WrongCredentialsCombination::class);
    }

    public function let(): void
    {
        $this->beConstructedWith('connection', 'username');
    }

    public function it_provides_a_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('connection');
    }

    public function it_provides_a_username(): void
    {
        $this->username()->shouldReturn('username');
    }
}
