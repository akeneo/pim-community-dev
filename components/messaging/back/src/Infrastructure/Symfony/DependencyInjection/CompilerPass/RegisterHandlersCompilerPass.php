<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\DependencyInjection\CompilerPass;

use Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\Command\ProcessMessage;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterHandlersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $processMessageCommandDefinition = $container->getDefinition(ProcessMessage::class);
        $handlersIds = $container->findTaggedServiceIds('akeneo.message_handler');

        foreach ($handlersIds as $handlerId => $tags) {
            foreach ($tags as $tag) {
                $processMessageCommandDefinition->addMethodCall('registerHandler', [
                    new Reference($handlerId), $tag['consumer']
                ]);
            }
        }
    }
}
