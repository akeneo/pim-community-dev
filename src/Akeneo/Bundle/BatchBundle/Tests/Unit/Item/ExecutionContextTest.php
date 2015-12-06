<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Item;

use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;

/**
 * Tests related to the ExecutionContext class
 *
 */
class ExecutionContextTest extends \PHPUnit_Framework_TestCase
{
    protected $executionContext = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->executionContext = new ExecutionContext();
    }

    public function testIsDirty()
    {
        $this->assertFalse($this->executionContext->isDirty());
        $this->executionContext->put('test_key', 'test_value');
        $this->assertTrue($this->executionContext->isDirty());
    }

    public function testClearDirtyFlag()
    {
        $this->executionContext->put('test_key', 'test_value');
        $this->assertTrue($this->executionContext->isDirty());
        $this->assertEntity($this->executionContext->clearDirtyFlag());
        $this->assertFalse($this->executionContext->isDirty());
    }

    public function testPut()
    {
        $this->assertEntity($this->executionContext->put('test_key', 'test_value'));
        $this->assertEquals('test_value', $this->executionContext->get('test_key'));
    }

    public function testGet()
    {
        $this->assertNull($this->executionContext->get('test_key'));
        $this->executionContext->put('test_key', 'test_value');
        $this->assertEquals('test_value', $this->executionContext->get('test_key'));
    }

    public function testRemove()
    {
        $this->assertNull($this->executionContext->get('test_key'));
        $this->executionContext->put('test_key', 'test_value');
        $this->assertEquals('test_value', $this->executionContext->get('test_key'));
        $this->assertEntity($this->executionContext->remove('test_key'));
        $this->assertNull($this->executionContext->get('test_key'));
    }

    public function testGetKeys()
    {
        $this->assertEmpty($this->executionContext->getKeys());
        $this->executionContext->put('test_key1', 'test_value1');
        $this->executionContext->put('test_key2', 'test_value2');
        $this->executionContext->put('test_key3', 'test_value3');
        $expectedKeys = array('test_key1', 'test_key2', 'test_key3');

        $this->assertEquals($expectedKeys, $this->executionContext->getKeys());
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Akeneo\Bundle\BatchBundle\Item\ExecutionContext', $entity);
    }
}
