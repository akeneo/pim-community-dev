<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Common;

use Oro\Bundle\GridBundle\Common\Collection;

class FieldDescriptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Collection();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param string $fieldName
     * @return mixed
     */
    protected function getTestElement($fieldName)
    {
        $result = $this->getMock('TestClass', array('getName'));
        $result->expects($this->any())->method('getName')->will($this->returnValue($fieldName));
        return $result;
    }

    public function testAdd()
    {
        $this->assertAttributeEmpty('elements', $this->model);
        $element = $this->getTestElement('element_name');
        $this->model->add($element);
        $this->assertAttributeEquals(array('element_name' => $element), 'elements', $this->model);
    }

    public function testGetElements()
    {
        $this->assertEmpty($this->model->getElements());
        $element = $this->getTestElement('element_name');
        $this->model->add($element);
        $this->assertEquals(array('element_name' => $element), $this->model->getElements());
    }

    public function testHas()
    {
        $this->assertFalse($this->model->has('element_name'));
        $this->model->add($this->getTestElement('element_name'));
        $this->assertTrue($this->model->has('element_name'));
    }

    public function testGet()
    {
        $element = $this->getTestElement('element_name');
        $this->model->add($element);
        $this->assertEquals($element, $this->model->get('element_name'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Element "foo" does not exist
     */
    public function testGetNoElement()
    {
        $this->assertEmpty($this->model->getElements());
        $this->model->get('foo');
    }

    public function testRemove()
    {
        $this->model->add($this->getTestElement('element_name'));
        $this->assertTrue($this->model->has('element_name'));
        $this->model->remove('element_name');
        $this->assertFalse($this->model->has('element_name'));
    }

    public function testOffsetExists()
    {
        $this->assertFalse($this->model->offsetExists('element_name'));
        $this->model->add($this->getTestElement('element_name'));
        $this->assertTrue($this->model->offsetExists('element_name'));
    }

    public function testOffsetGet()
    {
        $element = $this->getTestElement('element_name');
        $this->model->add($element);
        $this->assertEquals($element, $this->model->offsetGet('element_name'));
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage Cannot set value, use add
     */
    public function testOffsetSet()
    {
        $this->model->offsetSet(0, 'value');
    }

    public function testOffsetUnset()
    {
        $this->model->add($this->getTestElement('element_name'));
        $this->assertTrue($this->model->has('element_name'));
        $this->model->offsetUnset('element_name');
        $this->assertFalse($this->model->has('element_name'));
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->model->count());
        $this->assertCount($this->model->count(), $this->model->getElements());

        $this->model->add($this->getTestElement('element_name'));

        $this->assertEquals(1, $this->model->count());
        $this->assertCount($this->model->count(), $this->model->getElements());
    }

    public function testReorder()
    {
        $this->model->add($this->getTestElement('element_one'));
        $this->model->add($this->getTestElement('element_two'));
        $this->model->add($this->getTestElement('element_three'));

        $sourceOrder   = array('element_one', 'element_two', 'element_three'); // 1,2,3
        $expectedOrder = array('element_two', 'element_three', 'element_one'); // 2,3,1

        $this->assertEquals($sourceOrder, array_keys($this->model->getElements()));
        $this->model->reorder($expectedOrder);
        $this->assertEquals($expectedOrder, array_keys($this->model->getElements()));
    }

    public function testGetIterator()
    {
        $this->model->add($this->getTestElement('element_name'));

        $iterator = $this->model->getIterator();
        $this->assertInstanceOf('\ArrayIterator', $iterator);
        $this->assertEquals($this->model->getElements(), $iterator->getArrayCopy());
    }
}
