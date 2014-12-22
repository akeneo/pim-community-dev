<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// TODO : change the namespace for Akeneo\Bundle\RuleEngineBundle to prepare the use in DAM
namespace PimEnterprise\Bundle\RuleEngineBundle;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterRunnerPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PIM Enterprise Rule Engine Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PimEnterpriseRuleEngineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'PimEnterprise\Bundle\RuleEngineBundle\Model'
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
