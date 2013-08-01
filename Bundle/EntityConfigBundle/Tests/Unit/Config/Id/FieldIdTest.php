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
        $this->fieldId = new FieldId('testClass', 'testScope', 'testField', 'string');
    }

    public function testGetConfig()
    {
        $this->assertEquals('testClass', $this->fieldId->getClassName());
        $this->assertEquals('testScope', $this->fieldId->getScope());
        $this->assertEquals('testField', $this->fieldId->getFieldName());
        $this->assertEquals('string', $this->fieldId->getFieldType());
        $this->assertEquals('field_testScope_testClass_fieldName', $this->fieldId->getId());
    }

    public function testSerialize()
    {
        $this->assertEquals($this->fieldId, unserialize(serialize($this->fieldId)));
    }
}
