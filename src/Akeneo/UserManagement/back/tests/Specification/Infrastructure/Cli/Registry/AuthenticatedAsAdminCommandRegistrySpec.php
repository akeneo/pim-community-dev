<?php

declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Infrastructure\Cli\Registry;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticatedAsAdminCommandRegistrySpec extends ObjectBehavior
{
    public function it_registers_authenticated_as_admin_commands(): void
    {
        $this->isCommandAuthenticatedAsAdminUser('akeneo:batch:job')->shouldReturn(false);

        $this->registerCommand('akeneo:batch:job');
        $this->registerCommand('pim:install');

        $this->isCommandAuthenticatedAsAdminUser('akeneo:batch:job')->shouldReturn(true);
        $this->isCommandAuthenticatedAsAdminUser('debug:router')->shouldReturn(false);
    }
}
