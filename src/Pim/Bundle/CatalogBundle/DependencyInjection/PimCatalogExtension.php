<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Finder\Finder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimCatalogExtension extends Extension implements PrependExtensionInterface
{
    /** @staticvar string */
    const DOCTRINE_ORM = 'doctrine/orm';

    /** @staticvar string */
    const DOCTRINE_MONGODB_ODM = 'doctrine/mongodb-odm';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // process configuration to validation and merge
        $config = $this->processConfiguration(new Configuration(), $configs);

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
        $loader->load('factory.yml');
        $loader->load('entities.yml');

        if ($config['record_mails']) {
            $loader->load('mail_recorder.yml');
        }

        $this->loadValidationFiles($container);
        $this->loadStorageDriver($config, $container);
    }


    /**
     * Loads the validation files
     */
    protected function loadValidationFiles(ContainerBuilder $container)
    {
        // load validation files
        $dirs = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $dir = dirname($reflection->getFileName()) . '/Resources/config/validation';
            if (is_dir($dir)) {
                $dirs[] = $dir;
            }
        }
        $finder = new Finder();
        $mappingFiles = array();
        foreach ($finder->files()->in($dirs) as $file) {
            $mappingFiles[$file->getBasename('.yml')] = $file->getRealPath();
        }
        $container->setParameter(
            'validator.mapping.loader.yaml_files_loader.mapping_files',
            array_merge(
                $container->getParameter('validator.mapping.loader.yaml_files_loader.mapping_files'),
                array_values($mappingFiles)
            )
        );
    }

    /**
     * Provides the supported driver for product storage
     * @return string[]
     */
    protected function getSupportedStorageDrivers()
    {
        return array(self::DOCTRINE_ORM, self::DOCTRINE_MONGODB_ODM);
    }

    /**
     * Load the mapping for product and product storage
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadStorageDriver(array $config, ContainerBuilder $container)
    {
        $storageDriver = $config['storage_driver'];

        if (!in_array($storageDriver, $this->getSupportedStorageDrivers())) {
            throw new RuntimeException("The storage driver $storageDriver is not a supported drivers");
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load(sprintf('storage_driver/%s.yml', $storageDriver));

        $container->setParameter($this->getAlias().'.storage_driver', $storageDriver);
        // Parameter defining if the mapping driver must be enabled or not
        $container->setParameter($this->getAlias().'.storage_driver.'.$storageDriver, true);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $prependConfig = array(
            'TwigBundle'                     => 'twig',
            'AsseticBundle'                  => 'assetic',
            'DoctrineBundle'                 => 'doctrine',
            'KnpPaginatorBundle'             => 'knp_paginator',
            'FOSRestBundle'                  => 'fos_rest',
            'FOSJsRoutingBundle'             => 'fos_js_routing',
            'BeSimpleSoapBundle'             => 'be_simple_soap',
            'StofDoctrineExtensionsBundle'   => 'stof_doctrine_extensions',
            'EscapeWSSEAuthenticationBundle' => 'escape_wsse_authentication',
            'LiipImagineBundle'              => 'liip_imagine',
            'GenemuFormBundle'               => 'genemu_form',
            'OroSearchBundle'                => 'oro_search',
            'OroUIBundle'                    => 'oro_ui',
            'OroTranslationBundle'           => 'oro_translation',
            'JMSDiExtraBundle'               => 'jms_di_extra',
            'OroEntityExtendBundle'          => 'oro_entity_extend',
            'OroFilterBundle'                => 'oro_filter',
            'OroBatchBundle'                 => 'oro_batch',
            'KnpGaufretteBundle'             => 'knp_gaufrette',
        );

        foreach ($prependConfig as $bundle => $alias) {
            if (isset($bundles[$bundle])) {
                $this->prependExtensionConfig($container, $alias);
            }
        }
    }

    /**
     * Prepend configuration of a bundle to the container
     *
     * @param ContainerBuilder $container
     * @param string           $extensionAlias
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
