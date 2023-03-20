<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterNormalizersCompilerPass implements CompilerPassInterface
{
    private const DEFAULT_PRIORITY = 0;

    public function process(ContainerBuilder $container): void
    {
        $serializerDefinition = $container->getDefinition('akeneo_messenger.message.serializer');

        $services = $container->findTaggedServiceIds('akeneo_messenger.message.normalizer');
        $sortedServices = [];
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = $tag['priority'] ?? self::DEFAULT_PRIORITY;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }

        krsort($sortedServices);
        // Flatten the array
        $normalizers = \call_user_func_array('array_merge', $sortedServices);

        $serializerDefinition->setArguments([
            $normalizers,
            [new Definition(JsonEncoder::class, [])]
        ]);
    }
}
