<?php

namespace Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register locked jobs to the registry
 *
 * @author    Benoit Wannepain <bwannepain@kaliop.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegisterLockPass implements CompilerPassInterface
{
    const SUBSCRIBER_ID = 'akeneo_batch.lock_subscriber';
    const SERVICE_TAG = 'akeneo_batch.job';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SUBSCRIBER_ID)) {
            return;
        }

        $subscriberDefinition = $container->getDefinition(self::SUBSCRIBER_ID);
        foreach ($container->findTaggedServiceIds(self::SERVICE_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['lock']) && $tag['lock']) {
                    $serviceDefinition = $container->getDefinition($serviceId);
                    $subscriberDefinition->addMethodCall(
                        'registerJobCode',
                        [$serviceDefinition->getArgument(0)]
                    );
                }
            }
        }
    }
}
