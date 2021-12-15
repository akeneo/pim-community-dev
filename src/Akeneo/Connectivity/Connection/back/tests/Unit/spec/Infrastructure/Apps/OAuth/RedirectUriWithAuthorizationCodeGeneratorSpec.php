<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthenticationUser;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\AuthorizationCodeGeneratorInterface;
use PhpSpec\ObjectBehavior;

class RedirectUriWithAuthorizationCodeGeneratorSpec extends ObjectBehavior
{
    public function let(
        AuthorizationCodeGeneratorInterface $authorizationCodeGenerator
    ): void {
        $this->beConstructedWith(
            $authorizationCodeGenerator
        );
    }

    public function it_generates_a_redirect_uri_with_an_authorization_code(
        AuthorizationCodeGeneratorInterface $authorizationCodeGenerator,
        AppAuthorization $appAuthorization,
        AppConfirmation $appConfirmation,
        AppAuthenticationUser $appAuthenticationUser
    ): void {
        $code = 'MjE3NTE3Y';
        $redirectUriWithoutCode = 'https://foo.example.com/oauth/callback';
        $pimUserId = 1;

        $appAuthenticationUser->getPimUserId()->willReturn($pimUserId);
        $appAuthorization->getRedirectUri()->willReturn($redirectUriWithoutCode);
        $appAuthorization->getState()->willReturn(null);
        $authorizationCodeGenerator->generate(
            $appConfirmation,
            $pimUserId,
            $redirectUriWithoutCode
        )->willReturn($code);

        $this->generate($appAuthorization, $appConfirmation, $appAuthenticationUser)
            ->shouldReturn('https://foo.example.com/oauth/callback?code=MjE3NTE3Y');
    }

    public function it_generates_a_redirecturi_with_an_authorization_code_and_a_state(
        AuthorizationCodeGeneratorInterface $authorizationCodeGenerator,
        AppAuthorization $appAuthorization,
        AppConfirmation $appConfirmation,
        AppAuthenticationUser $appAuthenticationUser
    ): void {
        $code = 'MjE3NTE3Y';
        $state = 'NzFkOGRhOG';
        $redirectUriWithoutCode = 'https://foo.example.com/oauth/callback';
        $pimUserId = 1;

        $appAuthenticationUser->getPimUserId()->willReturn($pimUserId);
        $appAuthorization->getRedirectUri()->willReturn($redirectUriWithoutCode);
        $appAuthorization->getState()->willReturn($state);
        $authorizationCodeGenerator->generate(
            $appConfirmation,
            $pimUserId,
            $redirectUriWithoutCode
        )->willReturn($code);

        $this->generate($appAuthorization, $appConfirmation, $appAuthenticationUser)
            ->shouldReturn('https://foo.example.com/oauth/callback?code=MjE3NTE3Y&state=NzFkOGRhOG');
    }
}
