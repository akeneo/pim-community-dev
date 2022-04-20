<?php

declare(strict_types=1);

namespace Akeneo\Category\back\Infrastructure\Symfony;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\ResolveDoctrineTargetRepositoryPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Weasels
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
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

        $categoryMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Category\back\Domain\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $categoryMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
