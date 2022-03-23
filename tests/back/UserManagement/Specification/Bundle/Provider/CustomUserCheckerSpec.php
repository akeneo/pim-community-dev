<?php

namespace Specification\Akeneo\UserManagement\Bundle\Provider;

use Akeneo\UserManagement\Bundle\Provider\CustomUserChecker;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\DisabledException;

class CustomUserCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CustomUserChecker::class);
    }

    function it_throws_exception_if_user_is_disabled()
    {
        $user = new User();
        $user->setEnabled(false);
        $this->shouldThrow(DisabledException::class)->duringCheckPreAuth($user);
    }

    function it_passed_through_if_user_is_enabled()
    {
        $user = new User();
        $user->setEnabled(true);
        $this->shouldNotThrow(DisabledException::class)->duringCheckPreAuth($user);
    }
}
