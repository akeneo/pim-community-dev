<?php
namespace Pim\Bundle\SegmentationTreeBundle\Tests\Unit\Entity;

use Pim\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;

/**
 * Tests on AbstractSegment
 *
 *
 */
class AbstractSegmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractSegment $segment
     */
    protected $segment;

    /**
     * Create mock for segment abstract class
     * @return AbstractSegment
     */
    protected function createAbstractSegmentMock()
    {
        return $this->getMockForAbstractClass("Pim\Bundle\SegmentationTreeBundle\Entity\AbstractSegment");
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->segment = $this->createAbstractSegmentMock();
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->segment->getId());
    }

    /**
     * Test related method
     */
    public function testGetCode()
    {
        $code = "my code";
        $this->segment->setCode($code);
        $this->assertEquals($code, $this->segment->getCode());
    }

    /**
     * Test related method
     */
    public function testGetLeft()
    {
        $left = "8";
        $this->segment->setLeft($left);
        $this->assertEquals($left, $this->segment->getLeft());
    }

    /**
     * Test related method
     */
    public function testGetLevel()
    {
        $level = "5";
        $this->segment->setLevel($level);
        $this->assertEquals($level, $this->segment->getLevel());
    }

    /**
     * Test related method
     */
    public function testGetRight()
    {
        $right = "3";
        $this->segment->setRight($right);
        $this->assertEquals($right, $this->segment->getRight());
    }

    /**
     * Test related method
     */
    public function testGetRoot()
    {
        $root = "9";
        $this->segment->setRoot($root);
        $this->assertEquals($root, $this->segment->getRoot());
    }

    /**
     * Test related method
     */
    public function testGetParent()
    {
        $parentSegment = $this->createAbstractSegmentMock();
        $this->segment->setParent($parentSegment);
        $this->assertEquals($parentSegment, $this->segment->getParent());
    }

    /**
     * Test related method
     */
    public function testAddChild()
    {
        $childSegment = $this->createAbstractSegmentMock();
        $this->segment->addChild($childSegment);
        $children = $this->segment->getChildren();
        $this->assertEquals($childSegment, $children[0]);
    }

    /**
     * Test related method
     */
    public function testHasNotChildren()
    {
        $this->assertFalse($this->segment->hasChildren());
    }

    /**
     * Test related method
     */
    public function testHasChildren()
    {
        $childSegment = $this->createAbstractSegmentMock();
        $this->segment->addChild($childSegment);
        $this->assertTrue($this->segment->hasChildren());
    }

    /**
     * Test related method
     */
    public function testRemoveChild()
    {
        $childSegment = $this->createAbstractSegmentMock();
        $this->segment->addChild($childSegment);
        $this->assertTrue($this->segment->hasChildren());
        $this->segment->removeChild($childSegment);
        $this->assertFalse($this->segment->hasChildren());
    }

    /**
     * Test related method
     */
    public function testIsRoot()
    {
        $this->assertTrue($this->segment->isRoot());
    }

    /**
     * Test related method
     */
    public function testIsNotRoot()
    {
        $parentSegment = $this->createAbstractSegmentMock();
        $this->segment->setParent($parentSegment);
        $this->assertFalse($this->segment->isRoot());

    }
}
