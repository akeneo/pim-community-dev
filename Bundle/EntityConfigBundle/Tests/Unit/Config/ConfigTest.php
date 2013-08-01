<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityId;


class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = new Config(new EntityId('testClass', 'testScope'));
    }

    public function testValueConfig()
    {
        $values = array('firstKey' => 'firstValue', 'secondKey' => 'secondValue');
        $this->config->setValues($values);

        $this->assertEquals($values, $this->config->getValues());

        $this->assertEquals('firstValue', $this->config->get('firstKey'));
        $this->assertEquals('secondValue', $this->config->get('secondKey'));

        $this->assertEquals(true, $this->config->is('secondKey'));

        $this->assertEquals(true, $this->config->has('secondKey'));
        $this->assertEquals(false, $this->config->has('thirdKey'));

        $this->assertEquals(null, $this->config->get('thirdKey'));

        $this->config->set('secondKey', 'secondValue2');
        $this->assertEquals('secondValue2', $this->config->get('secondKey'));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->config->get('thirdKey', true);
    }
}
