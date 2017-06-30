<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use PimEnterprise\Bundle\ProductAssetBundle\Command\AddLocaleChannelToAssetsCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateMissingVariationFilesCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateVariationFileCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateVariationFilesFromReferenceCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\ProcessMassUploadCommand;
use PimEnterprise\Bundle\ProductAssetBundle\Command\SendAlertNotificationsCommand;
use PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler\RegisterMetadataBuildersPass;
use PimEnterprise\Bundle\ProductAssetBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Product asset management bundle
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class PimEnterpriseProductAssetBundle extends Bundle
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
            realpath(__DIR__ . '/Resources/config/model/doctrine') => 'PimEnterprise\Component\ProductAsset\Model'
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }

    /**
     * Explicitly register commands
     *
     * @param Application $application
     */
    public function registerCommands(Application $application)
    {
        $application->add(new GenerateMissingVariationFilesCommand());
        $application->add(new GenerateVariationFileCommand());
        $application->add(new GenerateVariationFilesFromReferenceCommand());
        $application->add(new SendAlertNotificationsCommand());
        $application->add(new AddLocaleChannelToAssetsCommand());
        $application->add(new ProcessMassUploadCommand());
    }
}
