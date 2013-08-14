<?php

namespace Oro\Bundle\ConfigBundle\Entity;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $object;

    protected function setUp()
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
    }

    public function testRecordId()
    {
        $object = $this->object;
        $id     = 5;

        $this->assertEmpty($object->getRecordId());

        $object->setRecordId($id);

        $this->assertEquals($id, $object->getRecordId());
    }

    public function testSettings()
    {
        $object   = $this->object;
        $settings = array(
            'oro_user' => array(
                'greeting' => true,
                'level'    => 10,
            )
        );

        $this->assertEmpty($object->getSettings());

        $object->setSettings($settings);

        $this->assertEquals($settings, $object->getSettings());
    }
}
