<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterCommandsThatNeedUserSystemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $listenerDefinition = $container->findDefinition('pim_user.event_listener.create_user_system');
        $commandServiceIds = $container->findTaggedServiceIds('console.command');

        foreach ($commandServiceIds as $commandServiceId => $tags) {
            if (isset($tags[0]['need_user_system']) && true === $tags[0]['need_user_system']) {
                $listenerDefinition->addMethodCall('registerCommand', [new Reference($commandServiceId)]);
            }
        }
    }
}
