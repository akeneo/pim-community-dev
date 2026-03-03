<?php

namespace Akeneo\UserManagement\Infrastructure\Cli\EventListener;

use Akeneo\UserManagement\Infrastructure\Cli\AuthenticateAdminUser;
use Akeneo\UserManagement\Infrastructure\Cli\Registry\AuthenticatedAsAdminCommandRegistry;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Create a user system for the commands that need to be authenticated as admin user before executing
 * This listener is called before each command.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticateCommandAsAdminUserListener
{
    public function __construct(
        private readonly AuthenticateAdminUser $authenticateAdminUser,
        private readonly AuthenticatedAsAdminCommandRegistry $commandRegistry,
    ) {
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function createUserSystem(ConsoleCommandEvent $event): void
    {
        if (!$this->commandRegistry->isCommandRegistered($event->getCommand()->getName())) {
            return;
        }

        ($this->authenticateAdminUser)();
    }
}
