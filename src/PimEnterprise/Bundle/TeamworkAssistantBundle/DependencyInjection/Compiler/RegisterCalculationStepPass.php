<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Get all calculation steps to add them the chained calculation step (only this one is declared as public service).
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class RegisterCalculationStepPass implements CompilerPassInterface
{
    /**
     * Default calculation step key.
     */
    const DEFAULT_CALCULATION_STEP = 'pimee_teamwork_assistant.calculation_step.chained';

    /**
     * All calculation step must be tagged with "pimee_teamwork_assistant.calculation_step".
     */
    const CALCULATION_STEP_TAG = 'pimee_teamwork_assistant.calculation_step';

    /**
     * Default priority if tagged service does not have it.
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::DEFAULT_CALCULATION_STEP)) {
            throw new \LogicException(sprintf(
                'No chained calculation step registered, please add a calculation step with %s as key',
                static::DEFAULT_CALCULATION_STEP
            ));
        }

        $calculationStepIds = $container->findTaggedServiceIds(static::CALCULATION_STEP_TAG);

        $calculationSteps = [];
        foreach ($calculationStepIds as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $calculationSteps[$priority][] = new Reference($serviceId);
            }
        }

        krsort($calculationSteps);
        $calculationSteps = call_user_func_array('array_merge', $calculationSteps);

        $container->getDefinition(static::DEFAULT_CALCULATION_STEP)->setArguments([$calculationSteps]);
    }
}
