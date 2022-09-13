<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;

class DeleteRunningUser
{
    public function __construct(
        private DeleteUserHandlerInterface $deleteUserHandler,
        private ResolveScheduledJobRunningUsername $resolveScheduledJobRunningUsername,
    ) {
    }

    public function execute(string $jobCode): void
    {
        $username = $this->resolveScheduledJobRunningUsername->fromJobCode($jobCode);
        $deleteUserCommand = new DeleteUserCommand($username);

        $this->deleteUserHandler->handle($deleteUserCommand);
    }
}
