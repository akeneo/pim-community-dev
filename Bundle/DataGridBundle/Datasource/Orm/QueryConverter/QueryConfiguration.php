<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class QueryConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('query')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('distinct')
                    ->defaultFalse()
                ->end()
                ->arrayNode('select')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('from')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('table')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('alias')->isRequired()->end()
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
     * @param  string $name Join type ('left', 'inner')
     *
     * @throws \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected function addJoinNode($name)
    {
        if (!in_array($name, ['left', 'inner'])) {
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
                    ->scalarNode('condition')->end()
                    ->scalarNode('conditionType')->end()
                ->end()
            ->end();
    }

    /**
     * @param  string $name Where type ('and', 'or')
     *
     * @throws InvalidConfigurationException
     * @return ArrayNodeDefinition
     */
    protected function addWhereNode($name)
    {
        if (!in_array($name, ['and', 'or'])) {
            throw new InvalidConfigurationException(sprintf('Invalid where type "%s"', $name));
        }

        $builder = new TreeBuilder();

        return $builder->root($name)
            ->requiresAtLeastOneElement()
            ->prototype('scalar')->end();
    }
}
