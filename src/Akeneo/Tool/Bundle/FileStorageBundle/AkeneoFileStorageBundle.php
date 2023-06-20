<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle;

use Akeneo\Tool\Bundle\FileStorageBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Akeneo\Tool\Bundle\FileStorageBundle\DependencyInjection\Compiler\SetLazyRootCreationToLocalStorageAdapterPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Akeneo File Storage Bundle.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoFileStorageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ResolveDoctrineTargetModelPass())
            ->addCompilerPass(new SetLazyRootCreationToLocalStorageAdapterPass());

        $mappings = [
            realpath(__DIR__.'/Resources/config/model/doctrine') => 'Akeneo\Tool\Component\FileStorage\Model',
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
