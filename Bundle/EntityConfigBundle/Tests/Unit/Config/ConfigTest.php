<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Config;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = new Config(new EntityConfigId('testClass', 'testScope'));
    }

    public function testCloneConfig()
    {
        $values = array('firstKey' => 'firstValue', 'secondKey' => new \stdClass());
        $this->config->setValues($values);

        $clone = clone $this->config;

        $this->assertTrue($this->config == $clone);
        $this->assertFalse($this->config === $clone);

    }

    public function testValueConfig()
    {
        $values = array('firstKey' => 'firstValue', 'secondKey' => 'secondValue', 'fourthKey' => new \stdClass());
        $this->config->setValues($values);

        $this->assertEquals($values, $this->config->all());
        $this->assertEquals(
            array('firstKey' => 'firstValue'),
            $this->config->all(
                function ($value) {
                    return $value == 'firstValue';
                }
            )
        );

        $this->assertEquals('firstValue', $this->config->get('firstKey'));
        $this->assertEquals('secondValue', $this->config->get('secondKey'));

        $this->assertEquals(true, $this->config->is('secondKey'));

        $this->assertEquals(true, $this->config->has('secondKey'));
        $this->assertEquals(false, $this->config->has('thirdKey'));

        $this->assertEquals(null, $this->config->get('thirdKey'));

        $this->assertEquals($this->config, unserialize(serialize($this->config)));

        $this->config->set('secondKey', 'secondValue2');
        $this->assertEquals('secondValue2', $this->config->get('secondKey'));

        $this->setExpectedException('Oro\Bundle\EntityConfigBundle\Exception\RuntimeException');
        $this->config->get('thirdKey', true);
    }
}
