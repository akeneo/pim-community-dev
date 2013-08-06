<?php

namespace Oro\Bundle\AsseticBundle\DependencyInjection;

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
class OroAsseticExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('oro_assetic.assets', $this->getAssets($container, $config));

        // choose dynamic or static
        if (!$container->getParameterBag()->resolveValue($container->getParameterBag()->get('assetic.use_controller'))) {
            $loader->load('assetic_controller_service.yml');
        }
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'oro_assetic';
    }

    /**
     * Get array with assets from config files
     *
     * @param ContainerBuilder $container
     * @param $config
     * @return array
     */
    public function getAssets(ContainerBuilder $container, $config)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $js = array();
        $css = array();

        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/assets.yml')) {
                $bundleConfig = Yaml::parse(realpath($file));
                if (isset($bundleConfig['css'])) {
                    $css = array_merge_recursive($css, $bundleConfig['css']);
                }
                if (isset($bundleConfig['js'])) {
                    $js = array_merge_recursive($js, $bundleConfig['js']);
                }
            }

        }

        $container->setParameter(
            'oro_assetic.assets_groups',
            array(
                'js' => array_keys($js),
                'css' => array_keys($css)
            )
        );

        $container->setParameter(
            'oro_assetic.compiled_assets_groups',
            array(
                'js' => $config['js_debug'],
                'css' => $config['css_debug']
            )
        );

        return array(
            'js' => $this->getAssetics($js, $config['js_debug'], $config['js_debug_all']),
            'css' => $this->getAssetics($css, $config['css_debug'], $config['css_debug_all']),
        );
    }

    protected function getAssetics($assetsArray, $debugBlocks, $debugAll)
    {
        $compressAssets = array();
        $uncompressAssets = array();
        foreach ($assetsArray as $blockName => $files) {
            if ($debugAll || in_array($blockName, $debugBlocks)) {
                $uncompressAssets = array_merge($uncompressAssets, $files);
            } else {
                $compressAssets = array_merge($compressAssets, $files);
            }
        }

        return array(
            'compress' => array($compressAssets),
            'uncompress' => array($uncompressAssets)
        );
    }
}
