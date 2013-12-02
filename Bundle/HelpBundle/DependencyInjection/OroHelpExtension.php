<?php

namespace Oro\Bundle\HelpBundle\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class OroHelpExtension extends Extension
{
    const BUNDLE_CONFIG_FILE = 'oro_help.yml';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->setConfigurationToLinkProvider($configs, $container);
    }

    /**
     * Reads configuration from all bundles and from the application and injects to oro_help.model.help_link_provider
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @return array
     */
    protected function setConfigurationToLinkProvider(array $configs, ContainerBuilder $container)
    {
        $applicationConfig = $this->processConfiguration(new ApplicationConfiguration(), $configs);
        $bundleConfig = $this->processConfiguration(new BundleConfiguration(), $this->getBundleConfigs($container));

        $configuration = array_merge_recursive($bundleConfig, $applicationConfig);

        $linkProvider = $container->getDefinition('oro_help.model.help_link_provider');
        $linkProvider->addMethodCall('setConfiguration', array($configuration));
    }

    /**
     * Get a list of configs from all bundles
     *
     * @param ContainerBuilder $container
     * @return array
     */
    protected function getBundleConfigs(ContainerBuilder $container)
    {
        $result = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $file = dirname($reflection->getFilename()) . '/Resources/config/' . self::BUNDLE_CONFIG_FILE;
            if (is_file($file)) {
                $file = realpath($file);
                $result[] = Yaml::parse($file);
                $container->addResource(new FileResource($file));
            }
        }
        return $result;
    }
}
