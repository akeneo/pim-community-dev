<?php

namespace Pim\Bundle\VersioningBundle;

use Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterUpdateGuessersPass;
use Pim\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterVersionPurgerAdvisorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Versioning Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimVersioningBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterSerializerPass('pim_versioning.serializer'))
            ->addCompilerPass(new RegisterUpdateGuessersPass())
            ->addCompilerPass(new RegisterVersionPurgerAdvisorPass());

        $versionMappings = [
            realpath(__DIR__.'/Resources/config/model/doctrine') => 'Akeneo\Tool\Component\Versioning\Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $versionMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
