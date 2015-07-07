<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle;

use Akeneo\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterRunnerPass;
use Akeneo\Bundle\RuleEngineBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Akeneo Rule Engine Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AkeneoRuleEngineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Bundle\RuleEngineBundle\Model'
        );

        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver(
                    $mappings,
                    array('doctrine.orm.entity_manager'),
                    false
                )
            )
            ->addCompilerPass(new RegisterRunnerPass())
        ;
    }
}
