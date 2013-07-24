<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\ItemMapping;
use Pim\Bundle\BatchBundle\Entity\FieldMapping;

/**
 * Test related class
 *
 *
 */
class FieldMappingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FieldMapping
     */
    protected $field;

    /**
     * Setup
     */
    public function setup()
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
        $this->assertFalse($this->field->getIsIdentifier());
        $this->assertNull($this->field->getItem());

        $this->field->setSource('my-code-src');
        $this->field->setDestination('my-code-dest');
        $this->field->setIsIdentifier(true);
        $item = new ItemMapping();
        $this->field->setItem($item);

        $this->assertEquals($this->field->getSource(), 'my-code-src');
        $this->assertEquals($this->field->getDestination(), 'my-code-dest');
        $this->assertTrue($this->field->getIsIdentifier());
        $this->assertEquals($this->field->getItem(), $item);
    }
}
