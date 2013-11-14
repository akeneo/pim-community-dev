<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class WorkflowConfiguration implements ConfigurationInterface
{
    const NODE_STEPS = 'steps';
    const NODE_ATTRIBUTES = 'attributes';
    const NODE_TRANSITIONS = 'transitions';
    const NODE_TRANSITION_DEFINITIONS = 'transition_definitions';

    /**
     * Processes and validates configuration
     *
     * @param array $configs
     * @return array
     */
    public function processConfiguration(array $configs)
    {
        $processor = new Processor();
        return $processor->processConfiguration($this, array($configs));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('configuration');
        $this->addWorkflowNodes($rootNode->children());

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $nodeBuilder
     * @return NodeBuilder
     */
    public function addWorkflowNodes(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->scalarNode('name')
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('label')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->enumNode('type')
                ->cannotBeEmpty()
                ->defaultValue(Workflow::TYPE_ENTITY)
                ->values(array(Workflow::TYPE_ENTITY, Workflow::TYPE_WIZARD))
            ->end()
            ->booleanNode('enabled')
                ->defaultTrue()
            ->end()
            ->scalarNode('start_step')
                ->defaultNull()
            ->end()
            ->append($this->getStepsNode())
            ->append($this->getAttributesNode())
            ->append($this->getTransitionsNode())
            ->append($this->getTransitionDefinitionsNode());

        return $nodeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    protected function getStepsNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_STEPS);
        $rootNode
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('template')
                        ->defaultNull()
                    ->end()
                    ->integerNode('order')
                        ->defaultValue(0)
                    ->end()
                    ->booleanNode('is_final')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('form_type')
                        ->defaultValue(WorkflowStepType::NAME)
                    ->end()
                    ->arrayNode('form_options')
                        ->prototype('variable')
                        ->end()
                        /** Cannot add specific nodes in form_options, because it can contain any value*/
                        /*->children()
                            ->arrayNode('attribute_fields')
                                ->prototype('variable')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('label')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('form_type')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->arrayNode('options')
                                            ->prototype('variable')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()*/
                    ->end()
                    ->arrayNode('allowed_transitions')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->arrayNode('view_attributes')
                        ->prototype('variable')
                            ->beforeNormalization()
                                ->always(
                                    function ($value) {
                                        if (!is_array($value)) {
                                            $value = array('attribute' => $value);
                                        }
                                        return $value;
                                    }
                                )
                            ->end()
                            ->validate()
                                ->always(
                                    function ($value) {
                                        if (!isset($value['attribute']) && !isset($value['path'])) {
                                            throw new \Exception('"attribute" or "path" is required option.');
                                        }
                                        if (!isset($value['attribute']) && !isset($value['label'])) {
                                            throw new \Exception('"label" is required when "attribute" is empty.');
                                        }
                                        foreach (array('path', 'attribute', 'label') as $option) {
                                            if (isset($value[$option]) && !is_string($value[$option])) {
                                                throw new \Exception(sprintf('Option "%s" must be a string.', $option));
                                            }
                                        }
                                        return $value;
                                    }
                                )
                            ->end()
                        ->end()
                        /** Cannot add specific nodes in form_options, because it can contain any value*/
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    /**
     * @return NodeDefinition
     */
    protected function getAttributesNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_ATTRIBUTES);
        $rootNode
            ->prototype('array')
                ->children()
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('options')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    /**
     * @return NodeDefinition
     */
    protected function getTransitionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_TRANSITIONS);
        $rootNode
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('step_to')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->booleanNode('is_start')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('is_hidden')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('is_unavailable_hidden')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('message')
                    ->end()
                    ->scalarNode('transition_definition')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('frontend_options')
                        ->prototype('variable')
                        ->end()
                    ->end()
                    ->scalarNode('form_type')
                        ->defaultValue(WorkflowTransitionType::NAME)
                    ->end()
                    ->arrayNode('form_options')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }

    /**
     * @return NodeDefinition
     */
    protected function getTransitionDefinitionsNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_TRANSITION_DEFINITIONS);
        $rootNode
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->arrayNode('conditions')
                        ->prototype('variable')
                        ->end()
                    ->end()
                    ->arrayNode('post_actions')
                        ->prototype('variable')
                        ->end()
                    ->end()
                    ->arrayNode('init_actions')
                        ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }
}
