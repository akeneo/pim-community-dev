<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\DTO;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppAuthorizationSpec extends ObjectBehavior
{
    public function it_is_an_app_authorization(): void
    {
        $this->shouldHaveType(AppAuthorization::class);
    }

    public function it_creates_from_request(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );
        $this->shouldBeAnInstanceOf(AppAuthorization::class);
    }

    public function it_creates_from_normalized(): void
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [
                [
                    'client_id' => 'a_client_id',
                    'authorization_scope' => 'an_authorization_scope',
                    'authentication_scope' => 'an_authentication_scope',
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a_state',
                ],
            ]
        );
        $this->shouldBeAnInstanceOf(AppAuthorization::class);
    }

    public function it_normalizes_app_authorization(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->normalize()->shouldReturn([
            'client_id' => 'a_client_id',
            'authorization_scope' => 'an_authorization_scope',
            'authentication_scope' => 'an_authentication_scope',
            'redirect_uri' => 'a_redirect_uri',
            'state' => 'a_state',
        ]);
    }

    public function it_gets_all_scopes(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->getAllScopes()->getScopes()->shouldReturn(['an_authentication_scope', 'an_authorization_scope']);
    }

    public function it_gets_only_authorization_scopes(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->getAuthorizationScopes()->getScopes()->shouldReturn(['an_authorization_scope']);
    }

    public function it_gets_only_authentication_scopes(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->getAuthenticationScopes()->getScopes()->shouldReturn(['an_authentication_scope']);
    }

    public function it_gets_state(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->getState()->shouldReturn('a_state');
    }

    public function it_gets_redirect_uri(): void
    {
        $this->beConstructedThrough(
            'createFromRequest',
            [
                'a_client_id',
                ScopeList::fromScopeString('an_authorization_scope'),
                ScopeList::fromScopeString('an_authentication_scope'),
                'a_redirect_uri',
                'a_state',
            ]
        );

        $this->getRedirectUri()->shouldReturn('a_redirect_uri');
    }
}
