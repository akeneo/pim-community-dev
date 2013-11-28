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
        $loader->load('guessers.yml');
        $loader->load('transformers.yml');

        $prefix = 'pim_installer_';
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
            'jobs'
        );
        $installerFiles = array();

        foreach ($entities as $entity) {
            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $file = dirname($reflection->getFilename()).'/Resources/config/'.$prefix.$entity.'.yml';
                if (is_file($file)) {
                    $installerFiles[$entity]= $file;
                }
            }
        }

        $container->setParameter('pim_installer.files', $installerFiles);
    }
}
