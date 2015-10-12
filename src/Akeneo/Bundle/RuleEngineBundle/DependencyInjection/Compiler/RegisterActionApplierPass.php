<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register all action applier by priority
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RegisterActionApplierPass extends AbstractOrderedPass
{
    /** @staticvar */
    const ACTION_APPLIER_REGISTRY_DEF = 'akeneo_rule_engine.action_applier.registry';

    /** @staticvar */
    const ACTION_APPLIER_TAG = 'akeneo_rule_engine.action_applier';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::ACTION_APPLIER_REGISTRY_DEF)) {
            return;
        }

        $registry = $container->getDefinition(self::ACTION_APPLIER_REGISTRY_DEF);
        $actionAppliers = $this->collectTaggedServices($container, self::ACTION_APPLIER_TAG);

        foreach ($actionAppliers as $actionApplier) {
            $registry->addMethodCall('addActionApplier', [$actionApplier]);
        }
    }
}
