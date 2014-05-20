<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Oro\Bundle\UserBundle\Entity\User;

class SecurityContextSpec extends ObjectBehavior
{
    function let(AuthenticationManagerInterface $authManager, AccessDecisionManagerInterface $adm)
    {
        $this->beConstructedWith($authManager, $adm, false);
    }

    function it_should_not_return_user_when_there_is_no_token()
    {
        $this->getUser()->shouldReturn(null);
    }

    function it_should_not_return_user_when_user_is_not_authenticated(TokenInterface $token)
    {
        $token->getUser()->willReturn(null);
        $this->setToken($token);

        $this->getUser()->shouldReturn(null);
    }

    function it_should_return_a_user_when_user_is_authenticated(TokenInterface $token, User $user)
    {
        $token->getUser()->willReturn($user);
        $this->setToken($token);

        $this->getUser()->shouldReturn($user);
    }
}
