<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\Annotation;

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
        $this->assertFalse($annotation->allowArray());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Help(array());

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
        $this->assertEquals(array($property => $value), $obj->getConfigurationArray());
    }

    public function propertiesDataProvider()
    {
        return array(
            array('alias', 'alias'),
            array('link', 'link'),
            array('prefix', 'prefix'),
            array('server', 'server'),
            array('uri', 'uri'),
        );
    }
}
