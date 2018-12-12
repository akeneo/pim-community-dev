<?php

declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\ACL\Application\Query;

use Akeneo\UserManagement\ACL\Application\Query\FindAuthenticatedUserIdentifier;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class FindAuthenticatedUserIdentifierSpec extends ObjectBehavior
{
    function let(UserContext $userContext)
    {
        $this->beConstructedWith($userContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindAuthenticatedUserIdentifier::class);
    }

    function it_finds_the_logged_in_user_identifier(UserContext $userContext)
    {
        $user = new User();
        $user->setUsername('julia');
        $userContext->getUser()->willReturn($user);

        $userIdentifier = $this->__invoke();

        $userIdentifier->stringValue()->shouldBe('julia');
    }

    function it_returns_null_if_there_is_no_logged_in_user(UserContext $userContext)
    {
        $userContext->getUser()->willReturn(null);
        $this->__invoke()->shouldReturn(null);
    }
}
