<?php

namespace Pim\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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

    protected $jobsConfig;

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
        $config = $this->processConfig(
            $this->yamlParser->parse(
                file_get_contents($configFile)
            )
        );

        foreach ($config['jobs'] as $alias => $job) {
            foreach ($job['steps'] as $step) {
                $definition->addMethodCall(
                    'addStepToJob',
                    array(
                        $config['name'],
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

    private function processConfig(array $config)
    {
        $processor = new Processor();
        if (!$this->jobsConfig) {
            $this->jobsConfig = $this->getJobsConfigTree();
        }

        return $processor->process($this->jobsConfig, $config);
    }

    private function getJobsConfigTree()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('connector');
        $root
            ->children()
                ->scalarNode('name')->end()
                ->arrayNode('jobs')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('title')->end()
                            ->scalarNode('type')->end()
                            ->arrayNode('steps')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('title')->end()
                                        ->scalarNode('reader')->end()
                                        ->scalarNode('processor')->end()
                                        ->scalarNode('writer')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder->buildTree();
    }
}
