<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Model\Mapping;

use Pim\Bundle\BatchBundle\Transform\Mapping\FieldMapping;

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
     * @return \Pim\Bundle\BatchBundle\Model\Mapping\FieldMappping
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
             ->setIsIdentifier(true);

        $this->assertEquals('test-source', $this->field->getSource());
        $this->assertEquals('test-destination', $this->field->getDestination());
        $this->assertTrue($this->field->getIsIdentifier());
    }
}
