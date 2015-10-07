<?php

namespace ConfigBundle\Tests\Unit\Utils;

use Oro\Bundle\ConfigBundle\Utils\TreeUtils;
use Oro\Bundle\ConfigBundle\Config\Tree\GroupNodeDefinition;

class TreeUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test group tree
     *
     * @return GroupNodeDefinition
     */
    protected static function getTestGroup()
    {
        $node1 = new GroupNodeDefinition('node1', array(), array());
        $node1->setLevel(1);
        $node2 = new GroupNodeDefinition('node2', array(), array());
        $node2->setLevel(2);
        $node3 = new GroupNodeDefinition('node3', array(), array($node2));
        $node3->setLevel(1);

        $root = new GroupNodeDefinition('node4', array(), array($node1, $node3));
        $root->setLevel(0);

        return $root;
    }

    public function testFindNodeByName()
    {
        // existing node
        $result = TreeUtils::findNodeByName(self::getTestGroup(), 'node2');
        $this->assertEquals('node2', $result->getName());

        // not found node
        $result = TreeUtils::findNodeByName(self::getTestGroup(), 'not_existed');
        $this->assertNull($result);
    }

    public function testGetByNestingLevel()
    {
        // existed nested node
        $result = TreeUtils::getByNestingLevel(self::getTestGroup(), 2);
        $this->assertEquals(2, $result->getLevel());
        $this->assertEquals('node2', $result->getName());

        // not found node
        $result = TreeUtils::getByNestingLevel(self::getTestGroup(), 5);
        $this->assertNull($result);
    }

    public function testGetFirstNodeName()
    {
        // not empty node
        $result = TreeUtils::getFirstNodeName(self::getTestGroup());
        $this->assertEquals('node1', $result);

        // empty node
        $result = TreeUtils::getFirstNodeName(new GroupNodeDefinition('some_name'));
        $this->assertNull($result);
    }
}
