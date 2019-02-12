<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Config\Tree;

use Oro\Bundle\ConfigBundle\Config\Tree\AbstractNodeDefinition;

class AbstractNodeDefinitionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'testNodeName';
    const TEST_PRIORITY = 255;

    /** @var AbstractNodeDefinition */
    protected $node;

    public function setUp(): void
    {
        $this->node = $this->getMockForAbstractClass(
            AbstractNodeDefinition::class,
            [self::TEST_NAME, []]
        );
    }

    public function tearDown()
    {
        unset($this->node);
    }

    public function testGetName()
    {
        $this->assertEquals(self::TEST_NAME, $this->node->getName());
    }

    public function testPrepareDefinition()
    {
        // should set default definition values
        $this->assertEquals(0, $this->node->getPriority());
    }

    public function testSetGetPriority()
    {
        $this->node->setPriority(self::TEST_PRIORITY);
        $this->assertEquals(self::TEST_PRIORITY, $this->node->getPriority());
    }
}
