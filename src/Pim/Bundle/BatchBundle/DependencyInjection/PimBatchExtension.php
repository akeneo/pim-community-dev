<?php
namespace Pim\Bundle\BatchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
#       $configuration = new Configuration();
#       $config = $this->processConfiguration($configuration, $configs);
        $config = $configs[0];

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $registry = $container->getDefinition('pim_batch.connectors');
        foreach ($config['jobs'] as $alias => $job) {
            $jobDef = new Definition('Pim\\Bundle\\BatchBundle\\Job\\SimpleJob', array($job['title']));
            foreach ($job['steps'] as $step) {
                $stepDef = new Definition('Pim\Bundle\BatchBundle\Step\ItemStep', array($step['title']));
                $stepDef->addMethodCall('setReader', array(new Reference($step['reader'])));
                $stepDef->addMethodCall('setProcessor', array(new Reference($step['processor'])));
                $stepDef->addMethodCall('setWriter', array(new Reference($step['writer'])));
                $jobDef->addMethodCall('addStep', array($stepDef));
            }
            $registry->addMethodCall('addJobToConnector', array($job['connector'], $job['type'], $alias, $jobDef));
        }
    }
}
