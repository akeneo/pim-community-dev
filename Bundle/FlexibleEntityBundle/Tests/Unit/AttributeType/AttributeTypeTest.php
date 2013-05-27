<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AttributeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider
     *
     * @return array
     *
     * @static
     */
    public static function typesProvider()
    {
        return array(
            array('Oro\Bundle\FlexibleEntityBundle\AttributeType\BooleanType', 'integer', 'option', 'oro_flexibleentity_boolean'),
            array('Oro\Bundle\FlexibleEntityBundle\AttributeType\TextType', 'varchar', 'text', 'oro_flexibleentity_text'),
            array('Oro\Bundle\FlexibleEntityBundle\AttributeType\TextAreaType', 'text', 'textarea', 'oro_flexibleentity_textarea')
        );
    }

    /**
     * Test related methods
     *
     * @param string $class
     * @param string $backend
     * @param string $form
     * @param string $name
     *
     * @dataProvider typesProvider
     */
    public function testConstructorAnGetters($class, $backend, $form, $name)
    {
        $attType = new $class($backend, $form);
        $this->assertEquals($attType->getName(), $name);
        $this->assertEquals($attType->getBackendType(), $backend);
        $this->assertEquals($attType->getFormType(), $form);
    }
}
