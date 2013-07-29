<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Twig;

use Pim\Bundle\ImportExportBundle\Twig\NormalizeConfigurationExtension;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizeConfigurationExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->extension = new NormalizeConfigurationExtension;
    }

    public function testInstanceOfTwigExtension()
    {
        $this->assertInstanceOf('\Twig_Extension', $this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('pim_ie_normalize_configuration', $this->extension->getName());
    }

    /**
     * @dataProvider getNormalizeValuesData
     */
    public function testNormalizeValues($value, $expectedValue)
    {
        $this->assertEquals($expectedValue, $this->extension->normalizeValueFilter($value));
    }

    public static function getNormalizeValuesData()
    {
        return array(
            array(true, 'Yes'),
            array(false, 'No'),
            array('foo', 'foo'),
            array(1, 1),
            array(null, 'N/A')
        );
    }

    /**
     * @dataProvider getNormalizeKeysData
     */
    public function testNormalizeKeys($key, $expectedKey)
    {
        $this->assertEquals($expectedKey, $this->extension->normalizeKeyFilter($key));
    }

    public static function getNormalizeKeysData()
    {
        return array(
            array('name', 'Name'),
            array('withHeader', 'With header'),
        );
    }
}

