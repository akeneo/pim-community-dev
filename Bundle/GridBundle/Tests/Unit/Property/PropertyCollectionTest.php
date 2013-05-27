<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Property\PropertyCollection;
use Oro\Bundle\GridBundle\Property\PropertyInterface;

class FieldDescriptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->collection = new PropertyCollection();
    }

    public function testAdd()
    {
        $property = $this->createProperty('propertyName');
        $this->collection->add($property);
        $this->assertEquals($property, $this->collection->get('propertyName'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Element must be an instance of Oro\Bundle\GridBundle\Property\PropertyInterface
     */
    public function testAddError()
    {
        $this->collection->add(new \stdClass());
    }

    private function createProperty($name)
    {
        $result = $this->getMockForAbstractClass('Oro\\Bundle\\GridBundle\\Property\\PropertyInterface');
        $result->expects($this->any())->method('getName')->will($this->returnValue($name));
        return $result;
    }
}
