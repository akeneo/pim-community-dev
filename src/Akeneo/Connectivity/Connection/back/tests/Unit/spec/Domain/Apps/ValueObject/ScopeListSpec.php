<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;

class ScopeListSpec extends ObjectBehavior
{
    public function it_is_a_scope_list(): void
    {
        $this->shouldHaveType(ScopeList::class);
    }

    public function it_is_instantiable_from_a_string_of_scopes(): void
    {
        $this->beConstructedThrough(
            'fromScopeString',
            [
                \sprintf(
                    "%s %s %s",
                    AuthenticationScope::SCOPE_EMAIL,
                    AuthenticationScope::SCOPE_PROFILE,
                    AuthenticationScope::SCOPE_OPENID
                ),
            ]
        );
    }

    public function it_is_instantiable_from_an_array_of_scopes(): void
    {
        $this->beConstructedThrough(
            'fromScopes',
            [
                [
                    AuthenticationScope::SCOPE_EMAIL,
                    AuthenticationScope::SCOPE_PROFILE,
                    AuthenticationScope::SCOPE_OPENID,
                ],
            ]
        );
    }

    public function it_gets_scopes(): void
    {
        $this->beConstructedThrough(
            'fromScopeString',
            [
                \sprintf(
                    "%s %s %s",
                    AuthenticationScope::SCOPE_EMAIL,
                    AuthenticationScope::SCOPE_PROFILE,
                    AuthenticationScope::SCOPE_OPENID
                ),
            ]
        );

        $this->getScopes()->shouldReturn([
            AuthenticationScope::SCOPE_EMAIL,
            AuthenticationScope::SCOPE_OPENID,
            AuthenticationScope::SCOPE_PROFILE,
        ]);
    }

    public function it_adds_scopes(): void
    {
        $this->beConstructedThrough(
            'fromScopes',
            [
                [
                    AuthenticationScope::SCOPE_EMAIL,
                    AuthenticationScope::SCOPE_PROFILE,
                    AuthenticationScope::SCOPE_OPENID,
                ],
            ]
        );

        $newScopesList = $this->addScopes(ScopeList::fromScopes(['new_scope', 'another_new_scope']));
        $newScopesList->getScopes()->shouldReturn([
            'another_new_scope',
            'email',
            'new_scope',
            'openid',
            'profile',
        ]);
    }

    public function it_tests_if_a_scope_belongs_to_scope_list(): void
    {
        $this->beConstructedThrough('fromScopes', [['scope']]);

        $this->hasScope('scope')->shouldReturn(true);
        $this->hasScope('not_found_scope')->shouldReturn(false);
    }

    public function it_gets_scopes_has_a_string(): void
    {
        $this->beConstructedThrough('fromScopes', [['a_scope', 'another_scope']]);
        $this->toScopeString()->shouldReturn("a_scope another_scope");
    }
}
