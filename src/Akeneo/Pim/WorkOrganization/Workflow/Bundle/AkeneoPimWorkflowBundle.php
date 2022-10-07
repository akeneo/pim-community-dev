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
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\ConfigureUserServicePass;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\RegisterProductDraftPresentersPass;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\RegisterProductProposalQueryFilterPass;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\RegisterPublishersPass;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelsPass;
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
            ->addCompilerPass(new ResolveDoctrineTargetModelsPass())
            ->addCompilerPass(new RegisterProductProposalQueryFilterPass('product_proposal'))
            ->addCompilerPass(new RegisterProductProposalQueryFilterPass('published_product'));
        ;

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
            ->addCompilerPass(new RegisterProductDraftPresentersPass())
            ->addCompilerPass(new RegisterPublishersPass())
            ->addCompilerPass(new ConfigureUserServicePass())
        ;
    }
}
