<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterRunnerPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\RegisterApplierPass;
use PimEnterprise\Bundle\RuleEngineBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelsPass;
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
            ->addCompilerPass(new ResolveDoctrineTargetModelsPass())
            ->addCompilerPass(
                DoctrineOrmMappingsPass::createYamlMappingDriver(
                    $mappings,
                    array('doctrine.orm.entity_manager'),
                    'pim_catalog.storage_driver.doctrine/orm'
                )
            )
            ->addCompilerPass(new RegisterRunnerPass())
        ;
    }
}
