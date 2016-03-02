<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Config\Definition\Builder;

use Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuTreeBuilder;

class MenuTreeBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MenuTreeBuilder
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = new MenuTreeBuilder();
    }

    public function testConstructor()
    {
        $nodeMapping = $this->readAttribute($this->builder, 'nodeMapping');
        $this->assertArrayHasKey('menu', $nodeMapping);
        $this->assertEquals(
            'Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuNodeDefinition',
            $nodeMapping['menu']
        );
    }

    public function testMenuNode()
    {
        $nodeDefinition = $this->builder->menuNode('test');
        $this->assertInstanceOf(
            'Oro\Bundle\NavigationBundle\Config\Definition\Builder\MenuNodeDefinition',
            $nodeDefinition
        );
        $this->assertEquals('test', $nodeDefinition->getNode()->getName());
    }
}
