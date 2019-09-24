<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle;

use Akeneo\Asset\Bundle\Command\CopyAssetFilesCommand;
use Akeneo\Asset\Bundle\Command\GenerateMissingVariationFilesCommand;
use Akeneo\Asset\Bundle\Command\GenerateVariationFileCommand;
use Akeneo\Asset\Bundle\Command\GenerateVariationFilesFromReferenceCommand;
use Akeneo\Asset\Bundle\Command\ProcessMassUploadCommand;
use Akeneo\Asset\Bundle\Command\SendAlertNotificationsCommand;
use Akeneo\Asset\Bundle\DependencyInjection\Compiler\ConfigureUserServicesPass;
use Akeneo\Asset\Bundle\DependencyInjection\Compiler\RegisterMetadataBuildersPass;
use Akeneo\Asset\Bundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Product asset management bundle
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class AkeneoAssetBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterMetadataBuildersPass())
            ->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $mappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'Akeneo\Asset\Component\Model'
        ];

        $container->addCompilerPass(new ConfigureUserServicesPass());
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
