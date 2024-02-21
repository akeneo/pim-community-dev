<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\TokenStorageDataCollector;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenStorageDataCollectorSpec extends ObjectBehavior
{
    function let(TokenStorageInterface $tokenStorage)
    {
        $this->beConstructedWith($tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TokenStorageDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
    }

    function it_collects_data_from_token_storage($tokenStorage, TokenInterface $token, UserInterface $user)
    {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(42);

        $this->collect()->shouldReturn(['pim_user_id' => 42 ]);
    }
}
