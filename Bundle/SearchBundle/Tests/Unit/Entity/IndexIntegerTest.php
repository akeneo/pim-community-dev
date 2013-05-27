<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexInteger;
use Oro\Bundle\SearchBundle\Entity\Item;

class IndexIntegerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexInteger
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexInteger();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_integer_field');
        $this->assertEquals('test_integer_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue(100);
        $this->assertEquals(100, $this->index->getValue());
    }

    public function testGetId()
    {
        $this->assertNull($this->index->getId());
    }

    public function testItem()
    {
        $this->assertNull($this->index->getItem());
        $item = new Item();
        $this->index->setItem($item);
        $this->assertEquals($item, $this->index->getItem());
    }
}
