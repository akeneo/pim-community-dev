<?php

namespace Akeneo\Tool\Bundle\VersioningBundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterUpdateGuessersPass;
use Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler\RegisterVersionPurgerAdvisorPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim Versioning Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoVersioningBundle extends Bundle
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
