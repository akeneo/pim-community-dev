<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Permission\Component\Query\GetViewableCategoryCodesForUserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryCodeViewFilterSpec extends ObjectBehavior
{
    function let(
        GetViewableCategoryCodesForUserInterface $getViewableCategoryCodesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(10);

        $this->beConstructedWith($getViewableCategoryCodesForUser, $tokenStorage);
    }

    function it_filters_category_codes_depending_on_user_permissions(
        GetViewableCategoryCodesForUserInterface $getViewableCategoryCodesForUser,
        TokenInterface $token,
        UserInterface $user
    ) {
        $token->getUser()->willReturn($user);

        $getViewableCategoryCodesForUser->forCategoryCodes(['master_women', 'camera'], 10)->willReturn(['master_women']);
        $getViewableCategoryCodesForUser->forCategoryCodes(['shoes', 'pant'], 10)->willReturn([]);

        $this->filter(['master_women', 'camera'])->shouldReturn(['master_women']);
        $this->filter(['shoes', 'pant'])->shouldReturn([]);
    }

    function it_throws_an_exception_if_user_is_not_authenticated(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ) {
        $token->getUser()->willReturn(null);
        $this
            ->shouldThrow(new \RuntimeException("Could not find any authenticated user"))
            ->during('filter', [['master_women', 'camera'], 10]);

        $tokenStorage->getToken()->willReturn(null);
        $this
            ->shouldThrow(new \RuntimeException("Could not find any authenticated user"))
            ->during('filter', [['master_women', 'camera'], 10]);
    }
}
