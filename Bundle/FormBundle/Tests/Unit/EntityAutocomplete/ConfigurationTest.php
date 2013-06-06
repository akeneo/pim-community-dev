<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete;

use Oro\Bundle\FormBundle\EntityAutocomplete\Configuration;
use Oro\Bundle\FormBundle\EntityAutocomplete\Property;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Autocomplete configuration for "unknown" is not found
     */
    public function testGetAutocompleteOptionsException()
    {
        $configuration = new Configuration(array());
        $configuration->getAutocompleteOptions('unknown');
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param string $name
     * @param array $expected
     */
    public function testGetAutocompleteOptions($options, $name, $expected)
    {
        $configuration = new Configuration($options);
        $this->assertEquals($expected, $configuration->getAutocompleteOptions($name));
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                array('test' => array('type' => 'flexible', 'entity_class' => 'Acme\TestBundle\Entity\Test')),
                'test',
                array('type' => 'flexible', 'entity_class' => 'Acme\TestBundle\Entity\Test')
            ),
            array(
                array('test' => array('type' => 'flexible', 'entity_class' => 'Acme\TestBundle\Entity\Test', 'properties' => array(array('name' => 'test')))),
                'test',
                array('type' => 'flexible', 'entity_class' => 'Acme\TestBundle\Entity\Test', 'properties' => array(new Property(array('name' => 'test'))))
            ),
        );
    }
}
