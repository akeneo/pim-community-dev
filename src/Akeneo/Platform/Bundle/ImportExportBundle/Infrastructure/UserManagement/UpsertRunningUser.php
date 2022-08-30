<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;

class UpsertRunningUser
{
    public function __construct(
        private UpsertUserHandlerInterface $upsertUserHandler,
        private ListUserRoleInterface $listUserRole,
        private ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ) {
    }

    public function execute(string $jobCode, array $userGroupCodes): void
    {
        $username = $this->resolveScheduledJobRunningUsername->fromJobCode($jobCode);
        $allRoleCodes = array_map(static fn (UserRole $role) => $role->getRole(), $this->listUserRole->all());

        $upsertUserCommand = UpsertUserCommand::job(
            $username,
            'fakepassword',
            sprintf('%s@example.com', $username),
            $jobCode,
            'Automated Job',
            $allRoleCodes,
            $userGroupCodes,
        );

        $this->upsertUserHandler->handle($upsertUserCommand);
    }
}
