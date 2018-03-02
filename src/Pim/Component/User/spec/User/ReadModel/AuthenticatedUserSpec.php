<?php

namespace spec\Pim\Component\User\ReadModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\User\ReadModel\AuthenticatedUser;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class AuthenticatedUserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(40, 'username', 'password', [['role' => 'ROLE_ADMIN']], true, 'salt', 'en_US');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticatedUser::class);
    }

    function it_is_an_user()
    {
        $this->shouldImplement(AdvancedUserInterface::class);
    }

    function it_is_serialisable()
    {
        $this->shouldImplement(\Serializable::class);
    }

    function it_has_an_id()
    {
        $this->getId()->shouldReturn(40);
    }

    function it_has_a_username()
    {
        $this->getUsername()->shouldReturn('username');
    }

    function it_has_password()
    {
        $this->getPassword()->shouldReturn('password');
    }

    function it_has_a_ui_locale()
    {
        $this->getUiLocale()->shouldReturn('en_US');
    }

    function it_has_roles()
    {
        $this->getRoles()->shouldBeArray();
    }

    function it_has_salt()
    {
        $this->getSalt()->shouldReturn('salt');
    }

    function it_remove_sensitive_data_from_the_user()
    {
        $this->eraseCredentials()->shouldReturn(null);
    }

    function it_has_a_non_expires_account()
    {
        $this->isAccountNonExpired()->shouldReturn(true);
    }

    function it_has_a_non_locked_account()
    {
        $this->isAccountNonLocked()->shouldReturn(true);
    }

    function it_has_a_password_request_non_expire()
    {
        $this->isCredentialsNonExpired()->shouldReturn(true);
    }

    function it_has_a_status()
    {
        $this->isEnabled()->shouldReturn(true);
    }

    function it_serializes_the_user()
    {
        $this->serialize()->shouldBeString();
    }

    function it_is_unserialize_the_users()
    {
        $this->unserialize('serialized user')->shouldReturn(null);
    }
}
