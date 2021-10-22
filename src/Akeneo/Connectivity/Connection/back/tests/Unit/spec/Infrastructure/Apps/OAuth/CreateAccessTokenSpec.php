<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateAccessToken;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use OAuth2\IOAuth2;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use OAuth2\OAuth2;
use PhpSpec\ObjectBehavior;

class CreateAccessTokenSpec extends ObjectBehavior
{
    public function let(IOAuth2 $auth2, IOAuth2GrantCode $storage, ClientProviderInterface $clientProvider): void
    {
        $this->beConstructedWith($auth2, $storage, $clientProvider);
    }

    public function it_is_a_create_access_token(): void
    {
        $this->shouldHaveType(CreateAccessToken::class);
        $this->shouldImplement(CreateAccessTokenInterface::class);
    }

    public function it_creates_an_access_token(
        IOAuth2 $auth2,
        IOAuth2GrantCode $storage,
        Client $client,
        IOAuth2AuthCode $authCode,
        ClientProviderInterface $clientProvider
    ): void {
        $clientProvider->findClientByAppId('client_id_1234')->willReturn($client);
        $storage->getAuthCode('auth_code_1234')->willReturn($authCode);
        $auth2->getVariable(OAuth2::CONFIG_ACCESS_LIFETIME)->willReturn(123);
        $auth2->getVariable(OAuth2::CONFIG_REFRESH_LIFETIME)->willReturn(456);

        $authCode->getData()->willReturn([]);
        $authCode->getScope()->willReturn('delete_products');
        $token = [
            'access_token' => 'generated_token_123',
            'token_type' => 'bearer',
        ];
        $auth2->createAccessToken($client, [])->willReturn($token);
        $auth2->setVariable(OAuth2::CONFIG_ACCESS_LIFETIME, null)->shouldBeCalled();

        $this->create('client_id_1234', 'auth_code_1234')->shouldReturn($token);
    }

    public function it_processes_only_valid_client(ClientProviderInterface $clientProvider): void
    {
        $clientProvider->findClientByAppId('client_id_1234')->willReturn(null);
        $this
            ->shouldThrow(
                new \InvalidArgumentException('No client found with the given client id.')
            )
            ->during('create', ['client_id_1234', 'auth_code_1234']);
    }

    public function it_processes_only_valid_auth_code(
        IOAuth2GrantCode $storage,
        Client $client,
        ClientProviderInterface $clientProvider
    ): void {
        $clientProvider->findClientByAppId('client_id_1234')->willReturn($client);
        $storage->getAuthCode('auth_code_1234')->willReturn(null);

        $this
            ->shouldThrow(
                new \InvalidArgumentException('Unknown authorization code.')
            )
            ->during('create', ['client_id_1234', 'auth_code_1234']);
    }
}
