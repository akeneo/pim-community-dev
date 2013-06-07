<?php
namespace Pim\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimProductExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('datagrid.yml');
        $loader->load('attribute_types.yml');
        $loader->load('orm_filter_types.yml');
//         $loader->load('form_types.yml');

        if (is_file($file = __DIR__.'/../Resources/config/attribute_types_properties.yml')) {
            $config = Yaml::parse(realpath($file));
            $container->setParameter('pim_product.attributes_config', $config);
        }

    }
}
