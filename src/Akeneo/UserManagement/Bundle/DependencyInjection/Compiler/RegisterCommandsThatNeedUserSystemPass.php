<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\DependencyInjection\Compiler;

use Akeneo\UserManagement\Infrastructure\Cli\Registry\AuthenticatedAsAdminCommandRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterCommandsThatNeedUserSystemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registerDefinition = $container->findDefinition(AuthenticatedAsAdminCommandRegistry::class);
        $commandServiceIds = $container->findTaggedServiceIds('akeneo.command.authenticated_as_admin_user');

        foreach (\array_keys($commandServiceIds) as $commandServiceId) {
            // Get the command name by calling the static method "getDefaultName" so the command service is not instantiated, because there are "lazy" by default
            $commandClass = $container->findDefinition($commandServiceId)->getClass();
            if (\method_exists($commandClass, 'getDefaultName') &&  null !== $commandClass::getDefaultName()) {
                $registerDefinition->addMethodCall('registerCommand', [$commandClass::getDefaultName()]);
            }
        }
    }
}
