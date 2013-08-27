<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ProcessorDecorator
{
    const ROOT = 'oro_system_configuration';

    /** @var Processor */
    protected $processor;

    /**
     * @param array $data
     * @return array
     */
    public function process(array $data)
    {
        $result = $this->getProcessor()->process($this->getConfigurationTree()->buildTree(), $data);

        return $result;
    }

    /**
     * Getter for processor
     *
     * @return Processor
     */
    protected function getProcessor()
    {
        return $this->processor ?: new Processor();
    }

    /**
     * Getter for configuration tree
     *
     * @return TreeBuilder
     */
    protected function getConfigurationTree()
    {
        $tree = new TreeBuilder();

        $tree->root(self::ROOT)->children();

        return $tree;
    }
}
