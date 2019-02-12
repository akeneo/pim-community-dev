<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $object;

    protected function setUp(): void
    {
        $this->object = new Config;
    }

    public function testGetId()
    {
        $this->assertNull($this->object->getId());
    }

    public function testEntity()
    {
        $object = $this->object;
        $entity = 'Oro\Entity';

        $this->assertEmpty($object->getEntity());

        $object->setEntity($entity);

        $this->assertEquals($entity, $object->getEntity());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $object->getValues());
    }

    public function testRecordId()
    {
        $object = $this->object;
        $id = 5;

        $this->assertEmpty($object->getRecordId());

        $object->setRecordId($id);

        $this->assertEquals($id, $object->getRecordId());
    }

    /**
     * Test getOrCreateValue
     */
    public function testGetOrCreateValue()
    {
        $object = $this->object;

        $value = $object->getOrCreateValue('pim_user', 'level');

        $this->assertEquals('pim_user', $value->getSection());
        $this->assertEquals('level', $value->getName());
        $this->assertEquals($object, $value->getConfig());

        $values = new ArrayCollection();
        $configValue = new ConfigValue();
        $configValue->setValue('test')
            ->setSection('test')
            ->setName('test');

        $values->add($configValue);
        $object->setValues($values);

        $value = $object->getOrCreateValue('test', 'test');

        $this->assertEquals('test', (string)$value);
        $this->assertEquals('test', $value->getSection());
        $this->assertEquals('test', $value->getName());
    }
}
