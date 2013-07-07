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
     *
     * @return array
     */
    public function getAssets(ContainerBuilder $container, $config)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $assets = array(
            'css' => array(),
            'js'  => array()
        );

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

        $compressJs = array();
        $uncompressJs = array();
        foreach ($js as $jsBlockName => $jsFiles) {
            if (in_array($jsBlockName, $config['uncompress_js'])) {
                $uncompressJs = array_merge($uncompressJs, $jsFiles);
            } else {
                $compressJs = array_merge($compressJs, $jsFiles);
            }
        }
        $assets['js'] = array(
            'compress' => array($compressJs),
            'uncompress' => array($uncompressJs)
        );

        $compressCss = array();
        $uncompressCss = array();
        foreach ($css as $cssBlockName => $cssFiles) {
            if (in_array($cssBlockName, $config['uncompress_css'])) {
                $uncompressCss = array_merge($uncompressCss, $cssFiles);
            } else {
                $compressCss = array_merge($compressCss, $cssFiles);
            }
        }
        $assets['css'] = array(
            'compress' => array($compressCss),
            'uncompress' => array($uncompressCss)
        );

        return $assets;
    }
}
