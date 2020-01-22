<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\UpdateUserPermissions;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateUserPermissionsSpec extends ObjectBehavior
{
    public function let(
        UserManager $userManager,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        ObjectUpdaterInterface $userUpdater
    ): void {
        $this->beConstructedWith($userManager, $roleRepository, $groupRepository, $userUpdater);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(UpdateUserPermissions::class);
        $this->shouldImplement(UpdateUserPermissionsInterface::class);
    }

    public function it_updates_user_permissions(
        $userManager,
        $roleRepository,
        $groupRepository,
        $userUpdater,
        UserInterface $user,
        RoleInterface $role,
        GroupInterface $group
    ) {
        $userId = new UserId(1234);
        $userRoleId = 321;
        $userGroupId = 456;

        $userManager->findUserBy(['id' => $userId->id()])->willReturn($user);
        $roleRepository->find($userRoleId)->willReturn($role);
        $groupRepository->find($userGroupId)->willReturn($group);

        $userUpdater->update(Argument::cetera())->shouldBeCalled();
        $userManager->updateUser(Argument::any())->shouldBeCalled();

        $this->execute($userId, $userRoleId, $userGroupId);
    }

    public function it_throws_an_exception_if_user_not_found($userManager, $userUpdater)
    {
        $userId = new UserId(1234);
        $userRoleId = 321;
        $userGroupId = 456;

        $userManager->findUserBy(['id' => $userId->id()])->willReturn(null);

        $userUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $userManager->updateUser(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('User with id "1234" not found.'))
            ->during('execute', [$userId, $userRoleId, $userGroupId]);
    }

    public function it_throws_an_exception_if_role_not_found($roleRepository, $userManager, $userUpdater, UserInterface $user)
    {
        $userId = new UserId(1234);
        $userRoleId = 321;
        $userGroupId = 456;

        $userManager->findUserBy(['id' => $userId->id()])->willReturn($user);
        $roleRepository->find($userRoleId)->willReturn(null);

        $userUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $userManager->updateUser(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('Role with id "321" not found.'))
            ->during('execute', [$userId, $userRoleId, $userGroupId]);
    }

    public function it_throws_an_exception_if_group_not_found(
        $groupRepository,
        $roleRepository,
        $userManager,
        $userUpdater,
        UserInterface $user,
        RoleInterface $role
    ) {
        $userId = new UserId(1234);
        $userRoleId = 321;
        $userGroupId = 456;

        $userManager->findUserBy(['id' => $userId->id()])->willReturn($user);
        $roleRepository->find($userRoleId)->willReturn($role);
        $groupRepository->find($userGroupId)->willReturn(null);

        $userUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $userManager->updateUser(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(new \InvalidArgumentException('Group with id "456" not found.'))
            ->during('execute', [$userId, $userRoleId, $userGroupId]);
    }
}
