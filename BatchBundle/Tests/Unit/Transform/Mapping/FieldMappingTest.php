<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Model\Mapping;

use Akeneo\Bundle\BatchBundle\Transform\Mapping\FieldMapping;

/**
 * Test related class
 *
 *
 */
class FieldMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldMappping
     */
    protected $field;

    /**
     * Create a Field Mapping
     *
     * @return \Akeneo\Bundle\BatchBundle\Model\Mapping\FieldMappping
     */
    protected function createFieldMapping()
    {
        $this->field = new FieldMapping();

        return $this->field;
    }

    /**
     * Test get/set methods of related class
     */
    public function testGetterSetter()
    {
        $this->createFieldMapping()
             ->setSource('test-source')
             ->setDestination('test-destination')
             ->setIdentifier(true);

        $this->assertEquals('test-source', $this->field->getSource());
        $this->assertEquals('test-destination', $this->field->getDestination());
        $this->assertTrue($this->field->isIdentifier());
    }
}
