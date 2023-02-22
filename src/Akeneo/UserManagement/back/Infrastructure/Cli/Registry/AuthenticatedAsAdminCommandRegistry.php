<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Infrastructure\Cli\Registry;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticatedAsAdminCommandRegistry
{
    private array $commandsAuthenticatedAsAdminUser = [];

    public function registerCommand(string $commandName): void
    {
        $this->commandsAuthenticatedAsAdminUser[] = $commandName;
    }

    public function isCommandAuthenticatedAsAdminUser(string $commandName): bool
    {
        return \in_array($commandName, $this->commandsAuthenticatedAsAdminUser);
    }
}
