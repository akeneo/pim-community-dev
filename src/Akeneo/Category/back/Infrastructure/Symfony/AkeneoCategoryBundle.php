<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony;

use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\RegisterCategoryItemCounterPass;
use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\RegisterPreviewGeneratorPass;
use Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Griffins
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoCategoryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new RegisterCategoryItemCounterPass())
            ->addCompilerPass(new RegisterPreviewGeneratorPass())
        ;

        $mappings = [
            realpath(__DIR__.'/Resources/config/doctrine/model/') => 'Akeneo\Category\Infrastructure\Component\Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false,
            ),
        );
    }
}
