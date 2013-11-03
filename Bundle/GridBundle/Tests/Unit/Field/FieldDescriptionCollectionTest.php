<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Field;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class FieldDescriptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldDescriptionCollection
     */
    protected $collection;

    protected function setUp()
    {
        $this->collection = new FieldDescriptionCollection();
    }

    public function testAdd()
    {
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('fieldName');
        $this->collection->add($fieldDescription);
        $this->assertEquals($fieldDescription, $this->collection->get('fieldName'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Element must be an instance of Oro\Bundle\GridBundle\Field\FieldDescriptionInterface
     */
    public function testAddError()
    {
        $this->collection->add(new \stdClass());
    }
}
