<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection;

use Akeneo\Bundle\StorageUtilsBundle\Storage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AkeneoStorageUtilsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        Storage::setStorageDriver($config['storage_driver']);

        $container->setParameter($this->getAlias().'.storage_driver', Storage::getStorageDriver());
        // Parameter defining if the mapping driver must be enabled or not
        $container->setParameter($this->getAlias().'.storage_driver.'. Storage::getStorageDriver(), true);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine.yml');
        $loader->load('factories.yml');

        Storage::loadStorageConfigFiles($container, __DIR__);
    }
}
