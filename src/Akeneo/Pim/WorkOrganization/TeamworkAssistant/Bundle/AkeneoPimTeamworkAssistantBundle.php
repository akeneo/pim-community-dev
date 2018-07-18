<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\RegisterCalculationStepPass;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\RegisterProjectRemoverPass;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AkeneoPimTeamworkAssistantBundle extends Bundle
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
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model',
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
