<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateUserPermissions implements UpdateUserPermissionsInterface
{
    /** @var UserManager */
    private $userManager;

    /** @var RoleRepository */
    private $roleRepository;

    /** @var GroupRepository */
    private $groupRepository;

    /** @var ObjectUpdaterInterface */
    private $userUpdater;

    public function __construct(
        UserManager $userManager,
        RoleRepository $roleRepository,
        GroupRepository $groupRepository,
        ObjectUpdaterInterface $userUpdater
    ) {
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->groupRepository = $groupRepository;
        $this->userUpdater = $userUpdater;
    }

    public function execute(UserId $userId, int $userRoleId, ?int $userGroupId): void
    {
        $user = $this->userManager->findUserBy(['id' => $userId->id()]);
        if (null === $user) {
            throw new \InvalidArgumentException(
                sprintf('User with id "%s" not found.', $userId->id())
            );
        }
        $data = ['roles' => [], 'groups' => []];

        /** @var ?RoleInterface */
        $role = $this->roleRepository->find($userRoleId);
        if (null === $role) {
            throw new \InvalidArgumentException(
                sprintf('Role with id "%s" not found.', $userRoleId)
            );
        }
        $data['roles'][] = $role->getRole();

        if (null !== $userGroupId) {
            /** @var ?GroupInterface */
            $group = $this->groupRepository->find($userGroupId);
            if (null === $group) {
                throw new \InvalidArgumentException(
                    sprintf('Group with id "%s" not found.', $userGroupId)
                );
            }
            $data['groups'][] = $group->getName();
        }

        $this->userUpdater->update($user, $data);

        $this->userManager->updateUser($user);
    }
}
