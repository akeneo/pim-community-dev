<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register all rule runners by priority
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RegisterRunnerPass extends AbstractOrderedPass
{
    const CHAINED_RUNNER_DEF = 'akeneo_rule_engine.runner.chained';
    const STRICT_CHAINED_RUNNER_DEF = 'akeneo_rule_engine.runner.strict_chained';
    const RUNNER_TAG = 'akeneo_rule_engine.runner';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHAINED_RUNNER_DEF)) {
            return;
        }

        $chainedLoader = $container->getDefinition(self::CHAINED_RUNNER_DEF);
        $strictChainedLoader = $container->getDefinition(self::STRICT_CHAINED_RUNNER_DEF);
        $loaders = $this->collectTaggedServices($container, self::RUNNER_TAG);

        foreach ($loaders as $loader) {
            $chainedLoader->addMethodCall('addRunner', [$loader]);
            $strictChainedLoader->addMethodCall('addRunner', [$loader]);
        }
    }
}
