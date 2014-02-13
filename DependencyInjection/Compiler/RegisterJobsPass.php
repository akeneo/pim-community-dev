<?php

namespace Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Read the batch_jobs.yml file of the connectors to register the jobs
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class RegisterJobsPass implements CompilerPassInterface
{
    /**
     * @var YamlParser
     */
    protected $yamlParser;

    /**
     * @var NodeInterface
     */
    protected $jobsConfig;

    /**
     * @param YamlParser $yamlParser
     */
    public function __construct($yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?: new YamlParser();
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('akeneo_batch.connectors');

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflClass = new \ReflectionClass($bundle);
            if (false === $bundleDir = dirname($reflClass->getFileName())) {
                continue;
            }
            if (is_file($configFile = $bundleDir.'/Resources/config/batch_jobs.yml')) {
                $container->addResource(new FileResource($configFile));
                $this->registerJobs($registry, $configFile);
            }
        }
    }

    /**
     * @param Definition $definition
     * @param string     $configFile
     */
    protected function registerJobs(Definition $definition, $configFile)
    {
        $config = $this->processConfig(
            $this->yamlParser->parse(
                file_get_contents($configFile)
            )
        );

        foreach ($config['jobs'] as $alias => $job) {
            foreach ($job['steps'] as $step) {

                $services = array();
                foreach ($step['services'] as $setter => $serviceId) {
                    $services[$setter]= new Reference($serviceId);
                }

                $parameters = array();
                foreach ($step['parameters'] as $setter => $value) {
                    $parameters[$setter] = $value;
                }

                $definition->addMethodCall(
                    'addStepToJob',
                    array(
                        $config['name'],
                        $job['type'],
                        $alias,
                        $job['title'],
                        $step['title'],
                        $step['class'],
                        $services,
                        $parameters
                    )
                );

                if ($job['templates']['show']) {
                    $definition->addMethodCall(
                        'setJobShowTemplate',
                        array($config['name'], $job['type'], $alias, $job['templates']['show'])
                    );
                }

                if ($job['templates']['edit']) {
                    $definition->addMethodCall(
                        'setJobEditTemplate',
                        array($config['name'], $job['type'], $alias, $job['templates']['edit'])
                    );
                }
            }
        }
    }

    /**
     * @param array $config
     */
    protected function processConfig(array $config)
    {
        $processor = new Processor();
        if (!$this->jobsConfig) {
            $this->jobsConfig = $this->getJobsConfigTree();
        }

        return $processor->process($this->jobsConfig, $config);
    }

    /**
     * @return NodeInterface
     */
    protected function getJobsConfigTree()
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
                            ->arrayNode('templates')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('show')->defaultNull()->end()
                                    ->scalarNode('edit')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->scalarNode('title')->end()
                            ->scalarNode('type')->end()
                            ->arrayNode('steps')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('title')->end()
                                        ->scalarNode('class')
                                            ->defaultValue('Akeneo\Bundle\BatchBundle\Step\ItemStep')
                                        ->end()
                                        ->arrayNode('services')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('parameters')
                                            ->prototype('scalar')->end()
                                        ->end()
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
