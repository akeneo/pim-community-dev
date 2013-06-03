<?php

namespace Oro\Bundle\FormBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

class OroFormExtension extends Extension
{
    const BUNDLE_CONFIG_FILE = 'form.yml';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $bundleConfigs = $this->getBundleConfigs($container);
        $configs = array_merge($bundleConfigs, $configs);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('oro_form.autocomplete.config', $config['autocomplete_entities']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('form_type.yml');
        $loader->load('autocomplete.yml');
    }

    /**
     * Get a list of configs from all bundles
     *
     * @param ContainerBuilder $container
     * @return array
     */
    protected function getBundleConfigs(ContainerBuilder $container)
    {
        $bundleConfigs = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $file = dirname($reflection->getFilename()) . '/Resources/config/' . self::BUNDLE_CONFIG_FILE;
            if (is_file($file)) {
                $file = realpath($file);
                $bundleConfigs[] = Yaml::parse($file);
                $container->addResource(new FileResource($file));
            }
        }
        return $bundleConfigs;
    }
}
