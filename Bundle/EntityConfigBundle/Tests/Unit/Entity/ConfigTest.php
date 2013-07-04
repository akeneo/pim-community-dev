<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigValue;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConfigEntity */
    private $configEntity;

    /** @var  ConfigField */
    private $configField;

    /** @var  ConfigValue */
    private $configValue;

    private $testClassName = 'Acme\Bundle\DemoBundle\Entity\TestAccount';

    protected  function setUp()
    {
        $this->configEntity = new ConfigEntity();
        $this->configField  = new ConfigField();
        $this->configValue  = new ConfigValue();
    }

    public function testProperties()
    {
        /** test ConfigEntity */
        $this->assertNull($this->configEntity->getClassName());
        $this->assertEmpty($this->configEntity->getId());

        $this->assertEquals(
            $this->testClassName,
            $this->configEntity->getClassName($this->configEntity->setClassName($this->testClassName))
        );

        /** test ConfigField */
        $this->assertEmpty($this->configField->getId());

        /** test ConfigValue */
        $this->assertEmpty($this->configValue->getId());
        $this->assertEmpty($this->configValue->getScope());
        $this->assertEmpty($this->configValue->getCode());
        $this->assertEmpty($this->configValue->getValue());
        $this->assertEmpty($this->configValue->getEntity());
        $this->assertEmpty($this->configValue->getField());
    }

    public function test()
    {
        $this->assertEquals(
            'test',
            $this->configField->getCode($this->configField->setCode('test'))
        );

        $this->assertEquals(
            'string',
            $this->configField->getType($this->configField->setType('string'))
        );

        /** test ConfigField set/getEntity */
        $this->configField->setEntity($this->configEntity);
        $this->assertEquals(
            $this->configEntity,
            $this->configField->getEntity()
        );

        /** test ConfigEntity addField */
        $this->configEntity->addField($this->configField);
        $this->assertEquals(
            $this->configField,
            $this->configEntity->getField('test')
        );

        /** test ConfigEntity setFields */
        $this->configEntity->setFields(array($this->configField));
        $this->assertEquals(
            array($this->configField),
            $this->configEntity->getFields()
        );

        /** test ConfigValue */
        $this->configValue
            ->setCode('is_extend')
            ->setValue(true)
            ->setScope('extend')
            ->setEntity($this->configEntity)
            ->setField($this->configField);

        $this->assertEquals($this->configEntity, $this->configValue->getEntity());
        $this->assertEquals($this->configField, $this->configValue->getField());

        $this->assertEquals(true, $this->configValue->getValue());
        $this->assertEquals('is_extend', $this->configValue->getCode());

        $this->assertEquals(
            array(
                'code'  => 'is_extend',
                'scope' => 'extend',
                'value' => true
            ),
            $this->configValue->toArray()
        );
    }
}
