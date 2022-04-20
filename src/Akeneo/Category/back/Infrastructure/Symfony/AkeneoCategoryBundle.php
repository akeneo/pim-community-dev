<?php

declare(strict_types=1);

namespace Akeneo\Category\back\Infrastructure\Symfony;


use Akeneo\Category\back\Infrastructure\Symfony\DependencyInjection\CompilerPass\RegisterCategoryItemCounterPass;
use Akeneo\Category\back\Infrastructure\Symfony\DependencyInjection\CompilerPass\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Weasels
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
        ;

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Category\back\Domain\Model'
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
