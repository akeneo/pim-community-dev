<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;

class UpsertRunningUser
{
    private const AUTOMATED_USER_PREFIX = 'job_automated_';

    public function __construct(
        private UpsertUserHandlerInterface $upsertUserHandler,
        private ListUserRoleInterface $listUserRole,
    ) {
    }

    public function execute(string $jobCode, array $userGroupCodes): void
    {
        $username = sprintf('%s%s', self::AUTOMATED_USER_PREFIX, $jobCode);
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
