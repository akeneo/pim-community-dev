<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PimEnterpriseTeamWorkAssistantBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResolveDoctrineTargetModelPass());
        $container->addCompilerPass(new RegisterCalculationStepPass());
        $container->addCompilerPass(new RegisterProjectRemoverPass());

        $mappingConfig = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'PimEnterprise\Component\TeamWorkAssistant\Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappingConfig,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
