<?php

namespace Pim\Bundle\InstallerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PimInstallerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('fixture_loader.yml');
        $this->addInstallerDataFiles($container);
    }

    /**
     * Prepare data files that installer takes in account
     *
     * @param ContainerBuilder $container
     */
    protected function addInstallerDataFiles(ContainerBuilder $container)
    {
        $dataParam = $container->getParameter('installer_data');
        preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $dataParam, $matches);
        $bundles    = $container->getParameter('kernel.bundles');
        $reflection = new \ReflectionClass($bundles[$matches['bundle']]);
        $dataPath   = dirname($reflection->getFilename()) . '/Resources/fixtures/' . $matches['directory'] . '/';

        $entities = array(
            'channels',
            'locales',
            'currencies',
            'families',
            'attribute_groups',
            'attributes',
            'categories',
            'group_types',
            'groups',
            'associations',
            'jobs',
            'products'
        );
        $installerFiles = array();

        foreach ($entities as $entity) {
            $file = $dataPath.$entity;
            foreach (array('.yml', '.csv') as $extension) {
                if (is_file($file . $extension)) {
                    $installerFiles[$entity] = $file . $extension;
                    break;
                }
            }
        }
        $container->setParameter('pim_installer.files', $installerFiles);
    }
}
