<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit\Annotation;

use Oro\Bundle\HelpBundle\Annotation\Help;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HelpTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAlias()
    {
        $annotation = new Help(array());
        $this->assertEquals(Help::ALIAS, $annotation->getAliasName());
    }

    public function testAllowArray()
    {
        $annotation = new Help(array());
        $this->assertTrue($annotation->allowArray());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param string $value
     * @param string $optionKey
     */
    public function testSettersAndGetters($property, $value, $optionKey)
    {
        $obj = new Help(array());

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
        $this->assertEquals(array($optionKey => $value), $obj->getConfigurationArray());
    }

    public function propertiesDataProvider()
    {
        return array(
            array('controllerAlias', 'controller', 'controller'),
            array('actionAlias', 'action', 'action'),
            array('bundleAlias', 'bundle', 'bundle'),
            array('vendorAlias', 'vendor', 'vendor'),
            array('link', 'link', 'link'),
            array('prefix', 'prefix', 'prefix'),
            array('server', 'server', 'server'),
            array('uri', 'uri', 'uri'),
        );
    }
}
