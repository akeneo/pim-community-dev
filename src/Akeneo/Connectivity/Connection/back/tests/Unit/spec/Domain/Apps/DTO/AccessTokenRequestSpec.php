<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use PhpSpec\ObjectBehavior;

class AccessTokenRequestSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'client_id_1234',
            'BTC_123_ETH',
            'authorization_code',
            'code_identifier_123',
            'code_challenge_123'
        );
    }

    public function it_is_an_access_token_request(): void
    {
        $this->shouldHaveType(AccessTokenRequest::class);
    }

    public function it_provides_a_client_id(): void
    {
        $this->getClientId()->shouldReturn('client_id_1234');
    }

    public function it_provides_an_authorization_code(): void
    {
        $this->getAuthorizationCode()->shouldReturn('BTC_123_ETH');
    }

    public function it_provides_a_grant_type(): void
    {
        $this->getGrantType()->shouldReturn('authorization_code');
    }

    public function it_provides_a_code_identifier(): void
    {
        $this->getCodeIdentifier()->shouldReturn('code_identifier_123');
    }

    public function it_provides_a_code_challenge(): void
    {
        $this->getCodeChallenge()->shouldReturn('code_challenge_123');
    }
}
