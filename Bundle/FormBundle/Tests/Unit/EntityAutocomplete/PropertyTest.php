<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param string $key
     * @param mixed $default
     * @param mixed $expected
     */
    public function testGetOption($options, $key, $default, $expected)
    {
        $property = new Property($options);
        $this->assertEquals($expected, $property->getOption($key, $default));
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return array(
            array(
                array('key' => 'value'), 'key', null, 'value'
            ),
            array(
                array('key' => 'value'), 'key', 'default', 'value'
            ),
            array(
                array('key2' => 'value'), 'key', 'default', 'default'
            )
        );
    }

    public function testGetName()
    {
        $options = array('name' => 'test');
        $property = new Property($options);
        $this->assertEquals('test', $property->getName());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Property option "unknown" is required.
     */
    public function testGetRequiredOptionException()
    {
        $options = array('name' => 'test');
        $property = new Property($options);
        $property->getRequiredOption('unknown');
    }

    public function testGetRequiredOption()
    {
        $options = array('name' => 'test');
        $property = new Property($options);
        $this->assertEquals('test', $property->getRequiredOption('name'));
    }
}
