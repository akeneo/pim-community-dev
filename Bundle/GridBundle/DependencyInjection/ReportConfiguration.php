<?php

namespace Oro\Bundle\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ReportConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('report')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('distinct')
                    ->defaultFalse()
                ->end()
                ->scalarNode('select')
                    ->defaultValue('*')
                ->end()
                ->arrayNode('from')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('table')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('alias')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('join')
                    ->append($this->addJoinNode('left'))
                    ->append($this->addJoinNode('inner'))
                ->end()
                ->arrayNode('where')
                    ->append($this->addWhereNode('and'))
                    ->append($this->addWhereNode('or'))
                ->end()
                ->scalarNode('groupBy')->end()
                ->scalarNode('having')->end()
                ->arrayNode('orderBy')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('column')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('dir')
                                ->defaultValue('asc')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }

    /**
     *
     * @param  string $name Join type ('left', 'inner')
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function addJoinNode($name)
    {
        if (!in_array($name, array('left', 'inner'))) {
            throw new InvalidConfigurationException(sprintf('Invalid join type "%s"', $name));
        }

        $builder = new TreeBuilder();

        return $builder->root($name)
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('join')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('alias')->end()
                ->end()
            ->end();
    }

    /**
     *
     * @param  string $name Where type ('and', 'or')
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function addWhereNode($name)
    {
        if (!in_array($name, array('and', 'or'))) {
            throw new InvalidConfigurationException(sprintf('Invalid where type "%s"', $name));
        }

        $builder = new TreeBuilder();

        return $builder->root($name)
            ->requiresAtLeastOneElement()
            ->prototype('scalar')
            ->end();
    }
}
