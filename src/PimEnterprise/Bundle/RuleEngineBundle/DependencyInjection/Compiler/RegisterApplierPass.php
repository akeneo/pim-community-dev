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
 * Register all rule runners by priority
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RegisterApplierPass extends AbstractOrderedPass
{
    /** @staticvar */
    const CHAINED_APPLIER_ID = 'pimee_rule_engine.applier.chained';

    /** @staticvar */
    const APPLIER_TAG = 'pimee_rule_engine.applier';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHAINED_APPLIER_ID)) {
            return;
        }

        $chainedLoader = $container->getDefinition(self::CHAINED_APPLIER_ID);
        $loaders = $this->findAndSortTaggedServices($container, self::APPLIER_TAG);

        foreach ($loaders as $loader) {
            $chainedLoader->addMethodCall('addAction', [$loader]);
        }
    }
}
