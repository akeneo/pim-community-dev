<?php

namespace Pim\Bundle\ConnectorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Connector bundle extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimConnectorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('array_converters.yml');
        $loader->load('factories.yml');
        $loader->load('items.yml');
        $loader->load('job_launchers.yml');
        $loader->load('models.yml');
        $loader->load('processors.yml');
        $loader->load('readers.yml');
        $loader->load('repositories.yml');
        $loader->load('savers.yml');
        $loader->load('steps.yml');
        $loader->load('writers.yml');
    }
}
