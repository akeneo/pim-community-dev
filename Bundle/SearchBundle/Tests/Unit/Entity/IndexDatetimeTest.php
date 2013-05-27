<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexDatetime;
use Oro\Bundle\SearchBundle\Entity\Item;

class IndexDatetimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexDatetime
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexDatetime();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_datetime_field');
        $this->assertEquals('test_datetime_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue(new \Datetime('2012-12-12'));
        $this->assertEquals('2012-12-12', $this->index->getValue()->format('Y-m-d'));
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
