<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\FileMetadataBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass to register Adapters
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterAdaptersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const ADAPTER_REGISTRY = 'akeneo_file_metadata.adapter.registry';

    /** @staticvar string */
    const ADAPTER_TAG = 'akeneo_file_metadata.adapter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::ADAPTER_REGISTRY);
        $adapters = $container->findTaggedServiceIds(self::ADAPTER_TAG);

        foreach (array_keys($adapters) as $adaptersId) {
            $registry->addMethodCall('add', [new Reference($adaptersId)]);
        }
    }
}
