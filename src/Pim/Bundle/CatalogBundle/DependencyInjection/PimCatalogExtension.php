<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Finder\Finder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimCatalogExtension extends Extension implements PrependExtensionInterface
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

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['TwigBundle'])) {
            $this->prependExtensionConfig($container, 'twig');
        }

        if (isset($bundles['AsseticBundle'])) {
            $this->prependExtensionConfig($container, 'assetic');
        }

        if (isset($bundles['DoctrineBundle'])) {
            $this->prependExtensionConfig($container, 'doctrine');
        }

        if (isset($bundles['KnpPaginatorBundle'])) {
            $this->prependExtensionConfig($container, 'knp_paginator');
        }

        if (isset($bundles['FOSRestBundle'])) {
            $this->prependExtensionConfig($container, 'fos_rest');
        }

        if (isset($bundles['FOSJsRoutingBundle'])) {
            $this->prependExtensionConfig($container, 'fos_js_routing');
        }

        if (isset($bundles['BeSimpleSoapBundle'])) {
            $this->prependExtensionConfig($container, 'be_simple_soap');
        }

        if (isset($bundles['StofDoctrineExtensionsBundle'])) {
            $this->prependExtensionConfig($container, 'stof_doctrine_extensions');
        }

        if (isset($bundles['EscapeWSSEAuthenticationBundle'])) {
            $this->prependExtensionConfig($container, 'escape_wsse_authentication');
        }

        if (isset($bundles['LiipImagineBundle'])) {
            $this->prependExtensionConfig($container, 'liip_imagine');
        }

        if (isset($bundles['GenemuFormBundle'])) {
            $this->prependExtensionConfig($container, 'genemu_form');
        }

        if (isset($bundles['OroSearchBundle'])) {
            $this->prependExtensionConfig($container, 'oro_search');
        }

        if (isset($bundles['OroUIBundle'])) {
            $this->prependExtensionConfig($container, 'oro_ui');
        }

        if (isset($bundles['OroUserBundle'])) {
            $this->prependExtensionConfig($container, 'oro_user');
        }

        if (isset($bundles['OroTranslationBundle'])) {
            $this->prependExtensionConfig($container, 'oro_translation');
        }

        if (isset($bundles['JMSDiExtraBundle'])) {
            $this->prependExtensionConfig($container, 'jms_di_extra');
        }

        if (isset($bundles['OroEntityExtendBundle'])) {
            $this->prependExtensionConfig($container, 'oro_entity_extend');
        }

        if (isset($bundles['OroFilterBundle'])) {
            $this->prependExtensionConfig($container, 'oro_filter');
        }

        if (isset($bundles['OroBatchBundle'])) {
            $this->prependExtensionConfig($container, 'oro_batch');
        }

        if (isset($bundles['KnpGaufretteBundle'])) {
            $this->prependExtensionConfig($container, 'knp_gaufrette');
        }
    }

    /**
     * Prepend configuration of a bundle to the container
     *
     * @param ContainerBuilder $container
     * @param string           $extensionAlias
     *
     */
    private function prependExtensionConfig(ContainerBuilder $container, $extensionAlias)
    {
        $container->prependExtensionConfig(
            $extensionAlias,
            $this->getBundleConfig($extensionAlias)
        );
    }

    /**
     * Get the bundle configuration from a file
     *
     * @param string $extensionAlias
     *
     * @return array
     */
    private function getBundleConfig($extensionAlias)
    {
        $configFile = realpath(
            sprintf('%s/../Resources/config/bundles/%s.yml', __DIR__, $extensionAlias)
        );

        if (!is_file($configFile)) {
            throw new \InvalidArgumentException(
                sprintf('Could not load file %s', $configFile)
            );
        }

        $yamlParser = new YamlParser();
        $config = $yamlParser->parse(file_get_contents($configFile));

        if (!array_key_exists($extensionAlias, $config)) {
            $configKeys = array_keys($config);

            throw new \RuntimeException(
                sprintf(
                    'Found file %s but it didn\'t start with "%s", got "%s" instead.',
                    $configFile,
                    $extensionAlias,
                    reset($configKeys)
                )
            );
        }

        return $config[$extensionAlias];
    }
}
