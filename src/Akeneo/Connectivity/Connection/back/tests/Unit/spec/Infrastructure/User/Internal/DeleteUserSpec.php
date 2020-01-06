<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteUserInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\DeleteUser;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteUserSpec extends ObjectBehavior
{
    public function let(UserRepositoryInterface $repository, RemoverInterface $remover): void
    {
        $this->beConstructedWith($repository, $remover);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(DeleteUser::class);
        $this->shouldImplement(DeleteUserInterface::class);
    }

    public function it_deletes_a_user($repository, $remover, User $user): void
    {
        $userId = new UserId(1);

        $repository->find($userId->id())->willReturn($user);
        $repository->find($userId->id())->shouldBeCalled();

        $remover->remove($user)->shouldBeCalled();

        $this->execute($userId);
    }

    public function it_throws_an_exception_if_user_not_found($repository, $remover)
    {
        $userId = new UserId(1);

        $repository->find($userId->id())->willReturn(null);
        $remover->remove(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('User with id "1" does not exist.'))
            ->during('execute', [$userId]);
    }
}
