<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register data providers.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterDataProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('akeneo.pim.automation.suggest_data.data_provider.registry');

        $taggedServiceIds = $container->findTaggedServiceIds('akeneo.pim.automation.suggest_data.data_provider');
        foreach ($taggedServiceIds as $serviceId => $tags) {
            if (!isset($tags[0]['alias'])) {
                throw new \Exception(sprintf('Tagged service "%s" needs an alias', $serviceId));
            }
            $definition->addMethodCall('addDataProvider', [$tags[0]['alias'], new Reference($serviceId)]);
        }
    }
}
