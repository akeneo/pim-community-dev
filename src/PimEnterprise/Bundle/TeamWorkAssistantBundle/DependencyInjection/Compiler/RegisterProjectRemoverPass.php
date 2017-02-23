<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass to register Projects Remover Rule
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class RegisterProjectRemoverPass implements CompilerPassInterface
{
    /** @staticvar string */
    const CHAINED_PROJECT_REMOVER = 'pimee_team_work_assistant.project_remover.chained';

    /** @staticvar string */
    const REMOVER_TAG = 'pimee_team_work_assistant.project_remover';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::CHAINED_PROJECT_REMOVER)) {
            throw new \LogicException(sprintf(
                'No chained project remover registered, please add a chained project remover with %s as key',
                static::CHAINED_PROJECT_REMOVER
            ));
        }

        $removers = [];
        $removersIds = array_keys($container->findTaggedServiceIds(static::REMOVER_TAG));
        foreach ($removersIds as $removerId) {
            $removers[] = new Reference($removerId);
        }

        $container->getDefinition(static::CHAINED_PROJECT_REMOVER)->setArguments([$removers]);
    }
}
