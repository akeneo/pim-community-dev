<?php
namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config\Tree;

use Oro\Bundle\ConfigBundle\Config\Tree\GroupNodeDefinition;

class GroupNodeDefinitionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'testNodeName';
    const TEST_LEVEL = 2;

    protected static function getTestGroup()
    {
        $node1 = new GroupNodeDefinition('node1', ['priority' => 255], []);
        $node3 = new GroupNodeDefinition('node3', ['priority' => 250], []);

        return new GroupNodeDefinition('node4', [], [$node1, $node3]);
    }

    public function testGetSetLevel()
    {
        $node = new GroupNodeDefinition(self::TEST_NAME);

        $this->assertEquals(0, $node->getLevel());

        $node->setLevel(self::TEST_LEVEL);
        $this->assertEquals(self::TEST_LEVEL, $node->getLevel());
    }

    public function testCount()
    {
        // empty node
        $node = new GroupNodeDefinition(self::TEST_NAME);
        $this->assertEquals(0, $node->count());

        // not empty node
        $node = self::getTestGroup();
        $this->assertEquals(2, $node->count());
    }

    public function testIsEmpty()
    {
        // empty node
        $node = new GroupNodeDefinition(self::TEST_NAME);
        $this->assertTrue($node->isEmpty());

        // not empty node
        $node = self::getTestGroup();
        $this->assertFalse($node->isEmpty());
    }

    public function testFirst()
    {
        // empty node
        $node = new GroupNodeDefinition(self::TEST_NAME);
        $this->assertFalse($node->first());

        // not empty node
        $node = self::getTestGroup();
        $this->assertEquals('node1', $node->first()->getName());
    }

    /**
     * @dataProvider nodeDefinitionProvider
     *
     * @param GroupNodeDefinition $node
     */
    public function testToBlockConfig(GroupNodeDefinition $node)
    {
        $result = $node->toBlockConfig();

        $this->assertArrayHasKey($node->getName(), $result);
        $result = $result[$node->getName()];

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('priority', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayNotHasKey('some_another', $result);
        $this->assertArrayNotHasKey('icon', $result);
    }

    /**
     * @dataProvider nodeDefinitionProvider
     *
     * @param GroupNodeDefinition $node
     */
    public function testToViewData(GroupNodeDefinition $node)
    {
        $result = $node->toViewData();

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('priority', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayNotHasKey('some_another', $result);
    }

    /**
     * @return array
     */
    public function nodeDefinitionProvider()
    {
        $node = new GroupNodeDefinition(
            self::TEST_NAME,
            [
                'title'        => 'some title',
                'priority'     => 123,
                'description'  => 'some desc',
                'icon'         => 'real icon',
                'some_another' => ''
            ]
        );

        return [
            [$node]
        ];
    }
}
