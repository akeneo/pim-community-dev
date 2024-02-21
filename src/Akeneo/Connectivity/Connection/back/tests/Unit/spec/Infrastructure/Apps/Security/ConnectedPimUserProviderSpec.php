<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectedPimUserProviderSpec extends ObjectBehavior
{
    public function let(TokenStorageInterface $tokenStorage): void
    {
        $this->beConstructedWith($tokenStorage);
    }

    public function it_is_a_connected_pim_user_provider(): void
    {
        $this->beAnInstanceOf(ConnectedPimUserProvider::class);
    }

    public function it_gets_current_user_id(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $userId = 1;
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn($userId);

        $this->getCurrentUserId()->shouldReturn($userId);
    }

    public function it_throws_an_exception_if_user_is_not_connected(
        TokenStorageInterface $tokenStorage
    ): void {
        $tokenStorage->getToken()->willReturn(null);
        $this->shouldThrow(\LogicException::class)->during('getCurrentUserId');
    }
}
