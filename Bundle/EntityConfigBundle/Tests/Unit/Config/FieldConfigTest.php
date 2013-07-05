<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class FieldConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldConfig
     */
    protected $fieldConfig;

    public function setUp()
    {
        $this->fieldConfig = new FieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', 'testScope');
    }

    public function testGetConfig()
    {
        $this->assertEquals(ConfigManagerTest::DEMO_ENTITY, $this->fieldConfig->getClassName());
        $this->assertEquals('testField', $this->fieldConfig->getCode());
        $this->assertEquals('string', $this->fieldConfig->getType());
        $this->assertEquals('testScope', $this->fieldConfig->getScope());
    }

    public function testSetConfig()
    {
        $this->fieldConfig->setClassName('testClass');
        $this->fieldConfig->setCode('testField2');
        $this->fieldConfig->setType('datetime');

        $this->assertEquals('testClass', $this->fieldConfig->getClassName());
        $this->assertEquals('testField2', $this->fieldConfig->getCode());
        $this->assertEquals('datetime', $this->fieldConfig->getType());
    }

    public function testValueConfig()
    {
        $values = array('firstKey' => 'firstValue', 'secondKey' => 'secondValue');
        $this->fieldConfig->setValues($values);

        $this->assertEquals($values, $this->fieldConfig->getValues());

        $this->assertEquals('firstValue', $this->fieldConfig->get('firstKey'));
        $this->assertEquals('secondValue', $this->fieldConfig->get('secondKey'));

        $this->assertEquals(true, $this->fieldConfig->is('secondKey'));

        $this->assertEquals(true, $this->fieldConfig->has('secondKey'));
        $this->assertEquals(false, $this->fieldConfig->has('thirdKey'));

        $this->assertEquals(null, $this->fieldConfig->get('thirdKey'));

        $this->fieldConfig->set('secondKey', 'secondValue2');
        $this->assertEquals('secondValue2', $this->fieldConfig->get('secondKey'));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->fieldConfig->get('thirdKey', true);
    }

    public function testSerialize()
    {
        $this->assertEquals($this->fieldConfig, unserialize(serialize($this->fieldConfig)));
    }
}
