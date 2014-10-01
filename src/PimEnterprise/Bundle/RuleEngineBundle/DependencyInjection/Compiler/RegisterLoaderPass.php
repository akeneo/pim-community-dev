<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register all rule loaders by priority
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RegisterLoaderPass extends AbstractOrderedPass
{
    /** @staticvar */
    const CHAINED_LOADER_DEF = 'pimee_rule_engine.loader.chained';

    /** @staticvar */
    const LOADER_TAG = 'pimee_rule_engine.loader';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHAINED_LOADER_DEF)) {
            return;
        }

        $chainedLoader = $container->getDefinition(self::CHAINED_LOADER_DEF);
        $loaders = $this->findAndSortTaggedServices($container, self::LOADER_TAG);

        foreach ($loaders as $loader) {
            $chainedLoader->addMethodCall('addLoader', [$loader]);
        }
    }
}
