<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('serializer.yml');

        $container->setParameter('pim_serializer.encoder.csv.delimiter', $config['encoders']['csv']['delimiter']);
        $container->setParameter('pim_serializer.encoder.csv.enclosure', $config['encoders']['csv']['enclosure']);
        $container->setParameter('pim_serializer.encoder.csv.with_header', $config['encoders']['csv']['with_header']);
    }
}
