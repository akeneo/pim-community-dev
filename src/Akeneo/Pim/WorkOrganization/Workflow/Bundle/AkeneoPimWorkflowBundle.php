<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PIM Enterprise Workflow Bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AkeneoPimWorkflowBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\ResolveDoctrineTargetModelsPass())
            ->addCompilerPass(new Compiler\RegisterProductProposalQueryFilterPass('product_proposal'));

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );

        $container
            ->addCompilerPass(new Compiler\RegisterProductDraftPresentersPass())
            ->addCompilerPass(new Compiler\RegisterPublishersPass())
            ->addCompilerPass(new DependencyInjection\Compiler\ConfigureUserServicePass())
            ->addCompilerPass(new DependencyInjection\Compiler\RegisterGetMetadataServicePass());
    }
}
