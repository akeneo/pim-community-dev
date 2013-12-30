<?php

namespace Pim\Bundle\JSONConnectorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class PimJSONConnectorExtension description
 * 
 * @copyright 2014 Sylvain Rascar <srascar@webnet.fr>
 * @author Sylvain Rascar <srascar@webnet.fr>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 */
class PimJSONConnectorExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('normalizers.yml');
        $loader->load('connectors.yml');
    }
}
