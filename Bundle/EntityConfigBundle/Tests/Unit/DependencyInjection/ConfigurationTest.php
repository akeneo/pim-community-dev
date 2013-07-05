<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ArrayNode;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        /** @var TreeBuilder $builder */
        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);

        /** @var ArrayNode $rootNode */
        $rootNode = $builder->buildTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $rootNode);

        $children = $rootNode->getChildren();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ScalarNode', $children['cache_dir']);
    }
}
