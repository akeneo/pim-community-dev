<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Entity;

use Akeneo\Bundle\BatchBundle\Entity\ItemMapping;
use Akeneo\Bundle\BatchBundle\Entity\FieldMapping;

/**
 * Test related class
 */
class ItemMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemMapping
     */
    protected $item;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->item = new ItemMapping();
    }

    /**
     * Test related methods
     */
    public function testGettersSetters()
    {
        $this->assertEquals(count($this->item->getFields()), 0);
        $this->assertEquals($this->item->getId(), 0);

        $field = new FieldMapping();
        $field->setSource('my-code-src');
        $field->setDestination('my-code-dest');
        $field->setIdentifier(true);
        $this->item->addField($field);

        $this->assertEquals(count($this->item->getFields()), 1);

        $this->item->removeField($field);
        $this->assertEquals(count($this->item->getFields()), 0);
    }
}
