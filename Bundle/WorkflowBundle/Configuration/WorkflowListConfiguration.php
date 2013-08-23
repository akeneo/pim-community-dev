<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class WorkflowListConfiguration implements ConfigurationInterface
{
    const NODE_WORKFLOWS = 'workflows';


    /**
     * Processes and validates configuration
     *
     * @param array $configs
     * @return array
     */
    public function processConfiguration(array $configs)
    {
        $processor = new Processor();
        return $processor->processConfiguration($this, $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $workflowConfig = new WorkflowConfiguration();

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_WORKFLOWS);
        $rootNode->useAttributeAsKey('name');
        $workflowConfig->addWorkflowNodes($rootNode->prototype('array')->children());

        return $treeBuilder;
    }
}
