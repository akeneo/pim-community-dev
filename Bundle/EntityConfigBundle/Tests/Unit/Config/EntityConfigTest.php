<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class EntityConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityConfig
     */
    protected $entityConfig;

    public function setUp()
    {
        $this->entityConfig = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'testScope');
    }

    public function testSetClassName()
    {
        $this->entityConfig->setClassName('testClass');
        $this->assertEquals('testClass', $this->entityConfig->getClassName());
    }

    public function testField()
    {
        $fieldConfig = new FieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', 'testScope');

        $this->entityConfig->addField($fieldConfig);

        $this->assertEquals(true, $this->entityConfig->hasField('testField'));
        $this->assertEquals($fieldConfig, $this->entityConfig->getField('testField'));

        $fieldConfig2 = new FieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField2', 'string', 'testScope');

        $this->entityConfig->setFields(array($fieldConfig2));

        $this->assertEquals(true, $this->entityConfig->hasField('testField2'));
        $this->assertEquals($fieldConfig2, $this->entityConfig->getField('testField2'));
    }
}
