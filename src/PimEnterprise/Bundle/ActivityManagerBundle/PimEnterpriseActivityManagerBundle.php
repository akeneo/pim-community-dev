<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle;

use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use PimEnterprise\Bundle\ActivityManagerBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PimEnterpriseActivityManagerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResolveDoctrineTargetModelPass());
        $container->addCompilerPass(new RegisterCalculationStepPass());

        $mappingConfig = [
            realpath(__DIR__.'/Resources/config/doctrine/model') => 'PimEnterprise\Component\ActivityManager\Model',
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
