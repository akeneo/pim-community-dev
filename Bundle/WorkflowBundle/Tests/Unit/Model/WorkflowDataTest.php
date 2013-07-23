<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class WorkflowDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowData
     */
    protected $data;

    protected function setUp()
    {
        $this->data = new WorkflowData();
    }

    public function testIsModified()
    {
        $this->assertFalse($this->data->isModified());
        $this->data->set('foo', 'bar');
        $this->assertTrue($this->data->isModified());

        $this->data = new WorkflowData(array('foo' => 'bar'));
        $this->assertFalse($this->data->isModified());
        $this->data->set('foo', 'bar');
        $this->assertFalse($this->data->isModified());
        $this->data->set('foo', 'baz');
        $this->assertTrue($this->data->isModified());
    }

    public function testHasGetSetRemove()
    {
        $this->assertFalse($this->data->has('foo'));
        $this->assertNull($this->data->get('foo'));

        $this->data->set('foo', 'bar');
        $this->assertTrue($this->data->has('foo'));
        $this->assertEquals('bar', $this->data->get('foo'));

        $this->data->remove('foo');
        $this->assertFalse($this->data->has('foo'));
        $this->assertNull($this->data->get('foo'));
    }

    public function testIssetGetSetUnset()
    {
        $this->assertFalse(isset($this->data->foo));
        $this->assertNull($this->data->foo);

        $this->data->foo = 'bar';
        $this->assertTrue(isset($this->data->foo));
        $this->assertEquals('bar', $this->data->foo);

        unset($this->data->foo);
        $this->assertFalse(isset($this->data->foo));
        $this->assertNull($this->data->foo);
    }

    public function testArrayAccess()
    {
        $this->assertInstanceOf('ArrayAccess', $this->data);

        $this->assertFalse(isset($this->data['foo']));
        $this->assertNull($this->data['foo']);

        $this->data['foo'] = 'bar';
        $this->assertTrue(isset($this->data['foo']));
        $this->assertEquals('bar', $this->data['foo']);

        unset($this->data['foo']);
        $this->assertFalse(isset($this->data['foo']));
        $this->assertNull($this->data['foo']);
    }

    public function testCount()
    {
        $this->assertEquals(0, count($this->data));

        $this->data->set('foo', 'bar');
        $this->assertEquals(1, count($this->data));

        $this->data->set('baz', 'qux');
        $this->assertEquals(2, count($this->data));

        $this->data->remove('foo');
        $this->assertEquals(1, count($this->data));

        $this->data->remove('baz');
        $this->assertEquals(0, count($this->data));
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->data->isEmpty());

        $this->data->set('foo', 'bar');
        $this->assertFalse($this->data->isEmpty());
    }

    public function testIterable()
    {
        $this->data->set('foo', 'bar');
        $this->data->set('baz', 'qux');

        $data = array();
        foreach ($this->data as $key => $value) {
            $data[$key] = $value;
        }

        $this->assertEquals(array('foo' => 'bar', 'baz' => 'qux'), $data);
    }
}
