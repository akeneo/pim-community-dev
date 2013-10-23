<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexText;
use Oro\Bundle\SearchBundle\Entity\Item;

class IndexTextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexText
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexText();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_text_field');
        $this->assertEquals('test_text_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue('test_text_value');
        $this->assertEquals('test_text_value', $this->index->getValue());
    }

    public function testValueWithHyphen()
    {
        $this->index->setValue('text-value');
        $this->assertEquals('text-value', $this->index->getValue());
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
