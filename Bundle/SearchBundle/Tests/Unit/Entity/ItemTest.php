<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\Item;
use Oro\Bundle\SearchBundle\Entity\IndexInteger;
use Oro\Bundle\SearchBundle\Entity\IndexText;
use Oro\Bundle\SearchBundle\Entity\IndexDatetime;
use Oro\Bundle\SearchBundle\Entity\IndexDecimal;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\Item
     */
    private $item;

    public function setUp()
    {
        $this->item = new Item();
    }

    public function testRecordId()
    {
        $this->assertNull($this->item->getRecordId());
        $this->item->setRecordId(2);
        $this->assertEquals(2, $this->item->getRecordId());
    }

    public function testChanged()
    {
        $this->assertEquals(false, $this->item->getChanged());
        $this->item->setChanged(true);
        $this->assertEquals(true, $this->item->getChanged());
    }

    public function testCreatedAt()
    {
        $this->assertNull($this->item->getCreatedAt());
        $this->item->setCreatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->item->getCreatedAt()->format('Y-m-d'));
    }

    public function testUpdatedAt()
    {
        $this->assertNull($this->item->getUpdatedAt());
        $this->item->setUpdatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->item->getUpdatedAt()->format('Y-m-d'));
    }

    public function testAlias()
    {
        $this->assertNull($this->item->getAlias());
        $this->item->setAlias('test alias');
        $this->assertEquals('test alias', $this->item->getAlias());
    }

    public function testGetId()
    {
        $this->assertNull($this->item->getId());
    }

    public function testEntity()
    {
        $this->assertNull($this->item->getEntity());
        $this->item->setEntity('test entity');
        $this->assertEquals('test entity', $this->item->getEntity());
    }

    public function testTitle()
    {
        $this->assertNull($this->item->getTitle());
        $this->item->setTitle('test title');
        $this->assertEquals('test title', $this->item->getTitle());
    }

    public function testIntegerField()
    {
        $fields = $this->item->getIntegerFields();
        $this->assertEquals(0, $fields->count());
        $index = new IndexInteger();
        $this->item->addIntegerField($index);
        $fields = $this->item->getIntegerFields();
        $this->assertEquals($index, $fields->get(0));
        $this->item->removeIntegerField($index);
        $fields = $this->item->getIntegerFields();
        $this->assertEquals(0, $fields->count());
    }

    public function testTextField()
    {
        $fields = $this->item->getTextFields();
        $this->assertEquals(0, $fields->count());
        $index = new IndexText();
        $index->setField(Indexer::TEXT_ALL_DATA_FIELD);
        $index->setValue('test text');
        $this->item->addTextField($index);
        $fields = $this->item->getTextFields();
        $this->assertEquals('test text', $this->item->getRecordText());
        $this->assertEquals($index, $fields->get(0));
        $this->item->removeTextField($index);
        $fields = $this->item->getTextFields();
        $this->assertEquals(0, $fields->count());

    }

    public function testDatetimeField()
    {
        $fields = $this->item->getDatetimeFields();
        $this->assertEquals(0, $fields->count());
        $index = new IndexDatetime();
        $this->item->addDatetimeField($index);
        $fields = $this->item->getDatetimeFields();
        $this->assertEquals($index, $fields->get(0));
        $this->item->removeDatetimeField($index);
        $fields = $this->item->getDatetimeFields();
        $this->assertEquals(0, $fields->count());
    }

    public function testDecimalField()
    {
        $fields = $this->item->getDecimalFields();
        $this->assertEquals(0, $fields->count());
        $index = new IndexDecimal();
        $this->item->addDecimalField($index);
        $fields = $this->item->getDecimalFields();
        $this->assertEquals($index, $fields->get(0));
        $this->item->removeDecimalField($index);
        $fields = $this->item->getDecimalFields();
        $this->assertEquals(0, $fields->count());
    }

    public function testBeforeSave()
    {
        $this->item->beforeSave();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdAt = $this->item->getCreatedAt();
        $this->assertEquals($date->format('Y-m-d'), $createdAt->format('Y-m-d'));
        $updatedAt = $this->item->getUpdatedAt();
        $this->assertEquals($date->format('Y-m-d'), $updatedAt->format('Y-m-d'));
    }

    public function testBeforeUpdate()
    {
        $this->item->beforeUpdate();
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $updatedAt = $this->item->getUpdatedAt();
        $this->assertEquals($date->format('Y-m-d'), $updatedAt->format('Y-m-d'));
    }

    public function testSaveItemData()
    {
        $this->item->saveItemData(
            array(
                'text' => array (
                    'test_field' => 'test text'
                ),
                'integer' => array(
                    'test_integer' => 10
                ),
                'datetime' => array(
                    'test_datetime' => new \DateTime('2013-01-01')
                ),
                'decimal' => array(
                    'test_decimal' => 10.26
                )
            )
        );

        $textFields = $this->item->getTextFields();
        $this->assertEquals('test text', $textFields->get(0)->getValue());
        $integerFields = $this->item->getIntegerFields();
        $this->assertEquals(10, $integerFields->get(0)->getValue());
        $datetimeFields = $this->item->getDatetimeFields();
        $this->assertEquals('2013-01-01', $datetimeFields->get(0)->getValue()->format('Y-m-d'));
        $decimalFields = $this->item->getDecimalFields();
        $this->assertEquals(10.26, $decimalFields->get(0)->getValue());
    }
}
