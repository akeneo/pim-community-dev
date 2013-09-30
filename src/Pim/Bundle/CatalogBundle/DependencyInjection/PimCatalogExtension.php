<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimCatalogExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // process configuration to validation and merge
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(
            'pim_catalog.imported_product_data_transformer',
            $config['imported_product_data_transformer']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('controllers.yml');
        $loader->load('forms.yml');
        $loader->load('form_types.yml');
        $loader->load('handlers.yml');
        $loader->load('managers.yml');
        $loader->load('datagrid.yml');
        $loader->load('attribute_types.yml');
        $loader->load('attribute_constraint_guessers.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }

        $yamlMappingFiles = $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files');

        $finder = new Finder();
        foreach ($finder->files()->in(__DIR__ . '/../Resources/config/validation') as $file) {
            $yamlMappingFiles[] = $file->getRealPath();
        }

        $container->setParameter('validator.mapping.loader.yaml_files_loader.mapping_files', $yamlMappingFiles);

        //Add DeleteException to list of safe message exceptions
        $exceptionCodes = $container->getParameter('fos_rest.exception.codes');
        $exceptionCodes['Pim\Bundle\CatalogBundle\Exception\DeleteException'] = 409;
        $container->setParameter('fos_rest.exception.codes', $exceptionCodes);

        $exceptionMessages = $container->getParameter('fos_rest.exception.messages');
        $exceptionMessages['Pim\Bundle\CatalogBundle\Exception\DeleteException'] = true;
        $container->setParameter('fos_rest.exception.messages', $exceptionMessages);
    }
}
