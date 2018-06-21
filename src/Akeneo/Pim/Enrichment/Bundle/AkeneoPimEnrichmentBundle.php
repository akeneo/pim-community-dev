<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoPimEnrichmentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
        ;

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine/Product') => 'Akeneo\Pim\Enrichment\Component\Product\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
