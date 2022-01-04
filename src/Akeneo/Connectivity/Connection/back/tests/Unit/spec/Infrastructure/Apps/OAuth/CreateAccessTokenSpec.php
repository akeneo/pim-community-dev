<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAccessTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetUserConsentedAuthenticationUuidQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateAccessToken;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateJsonWebToken;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RandomCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\GetAccessTokenQuery;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use OAuth2\IOAuth2GrantCode;
use OAuth2\Model\IOAuth2AuthCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateAccessTokenSpec extends ObjectBehavior
{
    public function let(
        IOAuth2GrantCode $storage,
        ClientProviderInterface $clientProvider,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        GetAppConfirmationQueryInterface $appConfirmationQuery,
        UserRepositoryInterface $userRepository,
        CreateJsonWebToken $createJsonWebToken,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        GetUserConsentedAuthenticationUuidQueryInterface $getUserConsentedAuthenticationUuidQuery,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        GetAccessTokenQuery $getAccessTokenQuery,
    ): void {
        $this->beConstructedWith(
            $storage,
            $clientProvider,
            $randomCodeGenerator,
            $appConfirmationQuery,
            $userRepository,
            $createJsonWebToken,
            $getConnectedAppScopesQuery,
            $getUserConsentedAuthenticationUuidQuery,
            $getUserConsentedAuthenticationScopesQuery,
            $getAccessTokenQuery,
        );
    }

    public function it_is_a_create_access_token(): void
    {
        $this->shouldHaveType(CreateAccessToken::class);
        $this->shouldImplement(CreateAccessTokenInterface::class);
    }

    public function it_creates_an_access_token(
        IOAuth2GrantCode $storage,
        Client $client,
        IOAuth2AuthCode $authCode,
        ClientProviderInterface $clientProvider,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        GetAppConfirmationQueryInterface $appConfirmationQuery,
        UserRepositoryInterface $userRepository,
        UserInterface $appUser,
        UserInterface $pimUser,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        GetAccessTokenQuery $getAccessTokenQuery,
    ): void {
        $clientProvider->findClientByAppId('client_id_1234')
            ->willReturn($client);
        $storage->getAuthCode('auth_code_1234')
            ->willReturn($authCode);
        $randomCodeGenerator->generate()
            ->willReturn('generated_token_123');
        $getConnectedAppScopesQuery->execute('client_id_1234')
            ->willReturn(['scope1', 'scope2']);
        $appConfirmationQuery->execute('client_id_1234')
            ->willReturn(AppConfirmation::create('client_id_1234', 1, 'some_user_group', 2));
        $userRepository->find(1)
            ->willReturn($appUser);
        $pimUser->getId()
            ->willReturn(2);
        $getUserConsentedAuthenticationScopesQuery->execute(2, 'client_id_1234')
            ->willReturn([]);

        $authCode->getScope()
            ->willReturn('delete_products');
        $authCode->getData()
            ->willReturn($pimUser);

        $token = [
            'access_token' => 'generated_token_123',
            'token_type' => 'bearer',
            'scope' => 'scope1 scope2',
        ];
        $getAccessTokenQuery->execute('client_id_1234', ['scope1', 'scope2'])->willReturn(null);

        $storage->createAccessToken(
            'generated_token_123',
            $client,
            $appUser,
            null,
            'scope1 scope2'
        )->shouldBeCalled();
        $storage->markAuthCodeAsUsed('auth_code_1234')->shouldBeCalled();

        $this->create('client_id_1234', 'auth_code_1234')->shouldReturn($token);
    }

    public function it_returns_the_existing_access_token_if_it_exists_with_the_same_scopes(
        IOAuth2GrantCode $storage,
        Client $client,
        IOAuth2AuthCode $authCode,
        ClientProviderInterface $clientProvider,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        UserInterface $pimUser,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        GetAccessTokenQuery $getAccessTokenQuery,
    ): void {
        $clientProvider->findClientByAppId('client_id_1234')
            ->willReturn($client);
        $storage->getAuthCode('auth_code_1234')
            ->willReturn($authCode);
        $getConnectedAppScopesQuery->execute('client_id_1234')
            ->willReturn(['scope1', 'scope2']);
        $getAccessTokenQuery->execute('client_id_1234', ['scope1', 'scope2'])
            ->willReturn('generated_token_123');
        $pimUser->getId()
            ->willReturn(2);
        $authCode->getData()
            ->willReturn($pimUser);
        $getUserConsentedAuthenticationScopesQuery->execute(2, 'client_id_1234')
            ->willReturn([]);

        $storage->createAccessToken(Argument::cetera())->shouldNotBeCalled();
        $storage->markAuthCodeAsUsed('auth_code_1234')->shouldBeCalled();

        $token = [
            'access_token' => 'generated_token_123',
            'token_type' => 'bearer',
            'scope' => 'scope1 scope2',
        ];
        $this->create('client_id_1234', 'auth_code_1234')->shouldReturn($token);
    }

    public function it_adds_an_id_token_to_the_access_token_if_openid_is_requested(
        ClientProviderInterface $clientProvider,
        Client $client,
        IOAuth2GrantCode $storage,
        IOAuth2AuthCode $authCode,
        RandomCodeGeneratorInterface $randomCodeGenerator,
        GetConnectedAppScopesQueryInterface $getConnectedAppScopesQuery,
        GetAppConfirmationQueryInterface $appConfirmationQuery,
        UserRepositoryInterface $userRepository,
        UserInterface $appUser,
        UserInterface $pimUser,
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        GetUserConsentedAuthenticationUuidQueryInterface $getUserConsentedAuthenticationUuidQuery,
        CreateJsonWebToken $createJsonWebToken
    ): void {
        $clientProvider->findClientByAppId('a_client_id')
            ->willReturn($client);
        $storage->getAuthCode('an_auth_code')
            ->willReturn($authCode);
        $randomCodeGenerator->generate()
            ->willReturn('a_token');
        $getConnectedAppScopesQuery->execute('a_client_id')
            ->willReturn(['an_authorization_scope']);
        $appConfirmationQuery->execute('a_client_id')
            ->willReturn(AppConfirmation::create('a_client_id', 1, 'a_user_group', 2));
        $userRepository->find(1)
            ->willReturn($appUser);
        $authCode->getData()
            ->willReturn($pimUser);
        $pimUser->getId()
            ->willReturn(2);
        $pimUser->getFirstName()
            ->willReturn('a_first_name');
        $pimUser->getLastName()
            ->willReturn('a_last_name');
        $pimUser->getEmail()
            ->willReturn('an_email');
        $getUserConsentedAuthenticationScopesQuery->execute(2, 'a_client_id')
            ->willReturn(['openid', 'an_authentication_scope']);
        $getUserConsentedAuthenticationUuidQuery->execute(2, 'a_client_id')
            ->willReturn('a_ppid');

        $storage->createAccessToken(
            'a_token',
            $client,
            $appUser,
            null,
            'an_authorization_scope'
        )
            ->shouldBeCalled();
        $storage->markAuthCodeAsUsed('an_auth_code')
            ->shouldBeCalled();
        $createJsonWebToken->create(
            'a_client_id',
            'a_ppid',
            ScopeList::fromScopeString('openid an_authentication_scope'),
            'a_first_name',
            'a_last_name',
            'an_email'
        )
            ->shouldBeCalled()
            ->willReturn('an_id_token');

        $expectedAccessToken = [
            'access_token' => 'a_token',
            'token_type' => 'bearer',
            'scope' => 'an_authentication_scope an_authorization_scope openid',
            'id_token' => 'an_id_token'
        ];

        $this->create('a_client_id', 'an_auth_code')
            ->shouldReturn($expectedAccessToken);
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
