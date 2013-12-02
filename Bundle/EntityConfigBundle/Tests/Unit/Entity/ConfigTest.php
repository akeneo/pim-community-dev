<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModelValue;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var  EntityConfigModel */
    private $configEntity;

    /** @var  FieldConfigModel */
    private $configField;

    /** @var  ConfigModelValue */
    private $configValue;

    protected function setUp()
    {
        $this->configEntity = new EntityConfigModel();
        $this->configField  = new FieldConfigModel();
        $this->configValue  = new ConfigModelValue();
    }

    public function testProperties()
    {
        /** test ConfigEntity */
        $this->assertNull($this->configEntity->getClassName());
        $this->assertEmpty($this->configEntity->getId());

        $this->assertNull($this->configEntity->getCreated());
        $this->configEntity->setCreated(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->configEntity->getCreated()->format('Y-m-d'));

        $this->assertNull($this->configEntity->getUpdated());
        $this->configEntity->setUpdated(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->configEntity->getUpdated()->format('Y-m-d'));

        /** test ConfigEntity prePersist */
        $this->configEntity->prePersist();
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($currentDate->format('Y-m-d'), $this->configEntity->getCreated()->format('Y-m-d'));

        /** test ConfigEntity preUpdate */
        $this->configEntity->preUpdate();
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($currentDate->format('Y-m-d'), $this->configEntity->getUpdated()->format('Y-m-d'));

        /** test ConfigField */
        $this->assertEmpty($this->configField->getId());

        $this->configField->setMode(ConfigModelManager::MODE_READONLY);
        $this->assertEquals(ConfigModelManager::MODE_READONLY, $this->configField->getMode());

        /** test ConfigValue */
        $this->assertEmpty($this->configValue->getId());
        $this->assertEmpty($this->configValue->getScope());
        $this->assertEmpty($this->configValue->getCode());
        $this->assertEmpty($this->configValue->getValue());
        $this->assertEmpty($this->configValue->getField());

        $this->assertFalse($this->configValue->getSerializable());

        $this->configValue->setSerializable(true);
        $this->assertTrue($this->configValue->getSerializable());


        $this->assertEmpty($this->configValue->getEntity());
        $this->configValue->setEntity($this->configEntity);
        $this->assertEquals(
            $this->configEntity,
            $this->configValue->getEntity()
        );
    }

    public function test()
    {
        $this->assertEquals(
            'test',
            $this->configField->getFieldName($this->configField->setFieldName('test'))
        );

        $this->assertEquals(
            'string',
            $this->configField->getType($this->configField->setType('string'))
        );

        $this->assertEquals(
            'Acme\Bundle\DemoBundle\Entity\TestAccount',
            $this->configEntity->getClassName(
                $this->configEntity->setClassName('Acme\Bundle\DemoBundle\Entity\TestAccount')
            )
        );

        /** test ConfigField set/getEntity */
        $this->configField->setEntity($this->configEntity);
        $this->assertEquals(
            $this->configEntity,
            $this->configField->getEntity()
        );

        /** test ConfigField getOptions */
        $this->assertInstanceOf(
            '\Doctrine\Common\Collections\ArrayCollection',
            $this->configField->getOptions()
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
            ->setScope('extend')
            ->setValue(true)
            ->setField($this->configField);

        $this->assertEquals(
            array(
                'code'         => 'is_extend',
                'scope'        => 'extend',
                'value'        => true,
                'serializable' => false
            ),
            $this->configValue->toArray()
        );

        /** test Config setValues() */
        $this->configEntity->setValues(array($this->configValue));
        $this->assertEquals(
            $this->configValue,
            $this->configEntity->getValue('is_extend', 'extend')
        );
    }


    public function testToFromArray()
    {
        $this->configValue
            ->setCode('doctrine')
            ->setScope('datagrid')
            ->setValue('a:2:{s:4:"code";s:8:"test_001";s:4:"type";s:6:"string";}');

        $values       = array(
            'is_searchable' => true,
            'is_sortable'   => false,
            'doctrine'      => $this->configValue
        );
        $serializable = array(
            'doctrine' => true
        );

        $this->configField->addValue(new ConfigModelValue('is_searchable', 'datagrid', false));
        $this->configField->fromArray('datagrid', $values, $serializable);
        $this->assertEquals(
            array(
                'is_searchable' => 1,
                'is_sortable'   => 0,
                'doctrine'      => $this->configValue
            ),
            $this->configField->toArray('datagrid')
        );

        $this->configEntity->addValue(new ConfigModelValue('is_searchable', 'datagrid', false));
        $this->configEntity->fromArray('datagrid', $values, $serializable);
        $this->assertEquals(
            array(
                'is_searchable' => 1,
                'is_sortable'   => 0,
                'doctrine'      => $this->configValue
            ),
            $this->configEntity->toArray('datagrid')
        );
    }
}
