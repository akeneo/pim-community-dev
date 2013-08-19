<?php

namespace Oro\Bundle\WorkflowBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

use Oro\Bundle\WorkflowBundle\Form\Type\OroWorkflowStep;

class ConfigurationTree
{
    const NODE_STEPS = 'steps';
    const NODE_ATTRIBUTES = 'attributes';
    const NODE_TRANSITIONS = 'transitions';
    const NODE_TRANSITION_DEFINITIONS = 'transition_definitions';

    /**
     * @var NodeDefinition[]
     */
    protected $nodeDefinitions;

    /**
     * @param array $configuration
     * @return array
     */
    public function parseConfiguration(array $configuration)
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('configuration');
        $nodeBuilder = $rootNode->children();
        foreach ($this->getNodeDefinitions() as $nodeDefinition) {
            $nodeBuilder->append($nodeDefinition);
        }

        $rootTree = $treeBuilder->buildTree();

        return $rootTree->finalize($configuration);
    }

    /**
     * @return NodeDefinition[]
     */
    public function getNodeDefinitions()
    {
        if (null === $this->nodeDefinitions) {
            $this->nodeDefinitions = array(
                self::NODE_STEPS                  => $this->getStepsNode(),
                self::NODE_ATTRIBUTES             => $this->getAttributesNode(),
                self::NODE_TRANSITIONS            => $this->getTransitionsNode(),
                self::NODE_TRANSITION_DEFINITIONS => $this->getTransitionDefinitionsNode()
            );
        }

        return $this->nodeDefinitions;
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
                        ->defaultValue(OroWorkflowStep::NAME)
                    ->end()
                    ->arrayNode('form_options')
                        ->children()
                            ->arrayNode('attribute_fields')
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
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('allowed_transitions')
                        ->prototype('scalar')
                        ->end()
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
        $allowedTypes = array('bool', 'boolean', 'int', 'integer', 'float', 'string', 'array', 'object', 'entity');
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NODE_ATTRIBUTES);
        $rootNode
            ->prototype('array')
                ->validate()
                    ->always(
                        function ($value) {
                            $classRequired = ($value['type'] == 'object' || $value['type'] == 'entity');
                            if ($classRequired && empty($value['options']['class'])) {
                                throw new \Exception(
                                    sprintf('Option "class" is required for type "%s"', $value['type'])
                                );
                            }
                            return $value;
                        }
                    )
                ->end()
                ->children()
                    ->scalarNode('label')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('type')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->validate()
                        ->ifNotInArray($allowedTypes)
                            ->thenInvalid('Invalid type %s, allowed types are "' . implode('", "', $allowedTypes) . '"')
                        ->end()
                    ->end()
                    ->arrayNode('options')
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
                    ->scalarNode('transition_definition')
                        ->isRequired()
                        ->cannotBeEmpty()
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
                    ->end()
                    ->arrayNode('post_actions')
                    ->end()
                ->end()
            ->end();

        return $rootNode;
    }
}
