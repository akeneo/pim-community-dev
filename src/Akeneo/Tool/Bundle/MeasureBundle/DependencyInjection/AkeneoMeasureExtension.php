<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * Load measure bundle configuration from any bundles
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AkeneoMeasureExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // retrieve each measure config from bundles
        $measuresConfig = [];
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/measure.yml')) {
                // merge measures configs
                if (empty($measuresConfig)) {
                    $measuresConfig = Yaml::parse(file_get_contents(realpath($file)));
                } else {
                    $entities = Yaml::parse(file_get_contents(realpath($file)));
                    foreach ($entities['measures_config'] as $family => $familyConfig) {
                        // merge result with already existing family config to add custom units
                        if (isset($measuresConfig['measures_config'][$family])) {
                            $measuresConfig['measures_config'][$family]['units'] =
                                array_merge(
                                    $measuresConfig['measures_config'][$family]['units'],
                                    $familyConfig['units']
                                );
                        } else {
                            $measuresConfig['measures_config'][$family] = $familyConfig;
                        }
                    }
                }
            }
        }
        $configs[] = $measuresConfig;
        // process configurations to validate and merge
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // load service
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        // set measures config
        $container->setParameter('akeneo_measure.measures_config', $config);

        $container
            ->getDefinition('akeneo_measure.manager')
            ->addMethodCall('setMeasureConfig', [$config['measures_config']]);
    }
}
