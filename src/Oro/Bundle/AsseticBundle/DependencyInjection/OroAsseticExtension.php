<?php

namespace Oro\Bundle\AsseticBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroAsseticExtension extends Extension
{
    private const DEFAULT_STYLESHEET_NAME = 'pim';

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

        $css = [];
        $stylesheets = [];

        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/assets.yml')) {
                $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));

                if (isset($bundleConfig['css'])) {
                    $css = array_merge_recursive($css, $bundleConfig['css']);
                }

                if (isset($bundleConfig['stylesheets'])) {
                    $stylesheets = array_merge_recursive($stylesheets, $bundleConfig['stylesheets']);
                }
            }
        }

        $allGroups = array_keys($css);

        if (empty($stylesheets)) {
            $stylesheets = [self::DEFAULT_STYLESHEET_NAME => ['groups' => $allGroups]];
        }

        $container->setParameter(
            'oro_assetic.assets_groups',
            ['css' => $allGroups]
        );

        $container->setParameter(
            'oro_assetic.compiled_assets_groups',
            ['css' => $config['css_debug']]
        );

        return [
            'css' => $this->getAssetics($css, $config['css_debug'], $config['css_debug_all'], $stylesheets),
        ];
    }

    protected function getAssetics(array $assets, array $debugGroups, bool $debugAll, array $stylesheets): array
    {
        $assetsGroupedByStylesheets = [];

        foreach ($stylesheets as $stylesheetName => $stylesheetConf) {
            if (!array_key_exists($stylesheetName, $assetsGroupedByStylesheets)) {
                $assetsGroupedByStylesheets[$stylesheetName] = [
                    'uncompress' => [],
                    'compress'   => [],
                ];
            }

            foreach ($assets as $groupName => $files) {
                if (!in_array($groupName, $stylesheetConf['groups'])) {
                    continue;
                }

                if ($debugAll || in_array($groupName, $debugGroups)) {
                    $assetsGroupedByStylesheets[$stylesheetName]['uncompress'] = array_merge(
                        $assetsGroupedByStylesheets[$stylesheetName]['uncompress'],
                        $files
                    );
                } else {
                    $assetsGroupedByStylesheets[$stylesheetName]['compress'] = array_merge(
                        $assetsGroupedByStylesheets[$stylesheetName]['compress'],
                        $files
                    );
                }
            }
        }

        return $assetsGroupedByStylesheets;
    }
}
