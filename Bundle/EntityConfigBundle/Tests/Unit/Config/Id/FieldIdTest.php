<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config\Id;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldId;

class FieldIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldId
     */
    protected $fieldId;

    public function setUp()
    {
        $this->fieldId = new FieldId('Test\Class', 'testScope', 'testField', 'string');
    }

    public function testGetConfig()
    {
        $this->assertEquals('Test\Class', $this->fieldId->getClassName());
        $this->assertEquals('testScope', $this->fieldId->getScope());
        $this->assertEquals('testField', $this->fieldId->getFieldName());
        $this->assertEquals('string', $this->fieldId->getFieldType());
        $this->assertEquals('field_testScope_Test_Class_fieldName', $this->fieldId->getId());
    }

    public function testSerialize()
    {
        $this->assertEquals($this->fieldId, unserialize(serialize($this->fieldId)));
    }
}
