<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Entity;

use Akeneo\Bundle\BatchBundle\Entity\ItemMapping;
use Akeneo\Bundle\BatchBundle\Entity\FieldMapping;

/**
 * Test related class
 */
class FieldMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldMapping
     */
    protected $field;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->field = new FieldMapping();
    }

    /**
     * Test related methods
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->field->getId());
        $this->assertNull($this->field->getSource());
        $this->assertNull($this->field->getDestination());
        $this->assertFalse($this->field->isIdentifier());
        $this->assertNull($this->field->getItem());

        $this->field->setSource('my-code-src');
        $this->field->setDestination('my-code-dest');
        $this->field->setIdentifier(true);
        $item = new ItemMapping();
        $this->field->setItem($item);

        $this->assertEquals($this->field->getSource(), 'my-code-src');
        $this->assertEquals($this->field->getDestination(), 'my-code-dest');
        $this->assertTrue($this->field->isIdentifier());
        $this->assertEquals($this->field->getItem(), $item);
    }
}
