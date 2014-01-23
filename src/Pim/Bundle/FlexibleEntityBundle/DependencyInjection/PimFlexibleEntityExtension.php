<?php

namespace Pim\Bundle\FlexibleEntityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * Flexible entity extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimFlexibleEntityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configs[] = $this->mergeFlexibleConfig($bundles);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('attribute_types.yml');

        $container->setParameter('pim_flexibleentity.flexible_config', $config);
    }

    /**
     * Merge flexible entity config
     *
     * @param array $bundles
     *
     * @return array
     */
    protected function mergeFlexibleConfig(array $bundles)
    {
        $entitiesConfig = [];
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/flexibleentity.yml')) {
                $bundleConfig = Yaml::parse(realpath($file));
                if (isset($bundleConfig['entities_config'])) {
                    foreach ($bundleConfig['entities_config'] as $entity => $entityConfig) {
                        $entitiesConfig['entities_config'][$entity] = $entityConfig;
                    }
                }
            }
        }

        return $entitiesConfig;
    }
}
