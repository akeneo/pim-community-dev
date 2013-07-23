<?php
namespace Pim\Bundle\BatchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Pim Batch bundle services configuration declaration
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimBatchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $configs[0];

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->createConnectors($config);
    }

    private function createConnectors($config)
    {
        foreach ($config['jobs'] as $job) {
           $def = new Definition('Pim\\Bundle\\BatchBundle\\Job\\SimpleJob', array($job['title']));

           foreach ($job['steps'] as $step) {
               $def->addMethodCall('setReader', $step['reader']);
               $def->addMethodCall('setProcessor', $step['processor']);
               $def->addMethodCall('setWriter', $step['writer']);
           }
        }
    }
}
