<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterWebhookEventDataBuilderPass implements CompilerPassInterface
{
    /** @staticvar string */
    const SERVICE_CHAINED = 'akeneo_connectivity.connection.webhook.event_data_builder_registry';

    /** @staticvar string */
    const SERVICE_TAG = 'akeneo_connectivity.connection.webhook_event_data_builder';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_CHAINED)) {
            return;
        }

        $service = $container->getDefinition(self::SERVICE_CHAINED);

        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('register', [new Reference($id)]);
        }
    }
}
