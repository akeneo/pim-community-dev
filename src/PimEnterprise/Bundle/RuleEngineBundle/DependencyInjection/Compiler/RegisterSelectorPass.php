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
class RegisterSelectorPass extends AbstractOrderedPass
{
    /** @staticvar */
    const CHAINED_SELECTOR_DEF = 'pimee_rule_engine.selector.chained';

    /** @staticvar */
    const SELECTOR_TAG = 'pimee_rule_engine.selector';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHAINED_SELECTOR_DEF)) {
            return;
        }

        $chainedLoader = $container->getDefinition(self::CHAINED_SELECTOR_DEF);
        $loaders = $this->findAndSortTaggedServices($container, self::SELECTOR_TAG);

        foreach ($loaders as $loader) {
            $chainedLoader->addMethodCall('addSelector', [$loader]);
        }
    }
}
