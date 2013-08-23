<?php

namespace Pim\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Read the jobs.yml file of the connectors to register the jobs
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterJobsPass implements CompilerPassInterface
{
    protected $yamlParser;

    public function __construct($yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?: new YamlParser();
    }

    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('pim_batch.connectors');

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflClass = new \ReflectionClass($bundle);
            if ($reflClass->isSubclassOf('Pim\\Bundle\\BatchBundle\\Connector\\Connector')) {
                if (false === $bundleDir = dirname($reflClass->getFileName())) {
                    continue;
                }
                if (is_file($configFile = $bundleDir.'/Resources/config/jobs.yml')) {
                    $this->registerJobs($registry, $configFile);
                }
            }
        }
    }

    private function registerJobs(Definition $definition, $configFile)
    {
        $config = $this->yamlParser->parse(file_get_contents($configFile));

        //TODO Validate jobs.yml structure
        foreach ($config['jobs'] as $alias => $job) {
            foreach ($job['steps'] as $step) {
                $definition->addMethodCall(
                    'addStepToJob',
                    array(
                        $job['connector'],
                        $job['type'],
                        $alias,
                        $job['title'],
                        $step['title'],
                        new Reference($step['reader']),
                        new Reference($step['processor']),
                        new Reference($step['writer']),
                    )
                );
            }
        }
    }
}
