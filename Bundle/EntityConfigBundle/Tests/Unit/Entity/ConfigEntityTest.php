<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Entity;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;

class ConfigEntityTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConfigEntity */
    private $configEntity;

    /** @var  ConfigField */
    private $configField;

    private $testClassName = 'Acme\Bundle\DemoBundle\Entity\TestAccount';

    protected  function setUp()
    {
        $this->configEntity = new ConfigEntity();
        $this->configField  = new ConfigField();
    }

    public function testProperties()
    {
        $this->assertNull($this->configEntity->getClassName());
        $this->assertEmpty($this->configEntity->getId());

        $this->assertEquals(
            $this->testClassName,
            $this->configEntity->getClassName($this->configEntity->setClassName($this->testClassName))
        );
    }

    public function testField()
    {
        $this->assertEmpty($this->configField->getId());

        $this->assertEquals(
            'test',
            $this->configField->getCode($this->configField->setCode('test'))
        );

        $this->assertEquals(
            'string',
            $this->configField->getType($this->configField->setType('string'))
        );

    }
}