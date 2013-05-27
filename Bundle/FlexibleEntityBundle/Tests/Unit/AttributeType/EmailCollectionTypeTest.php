<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\EmailCollectionType;

class EmailCollectionTypeTest extends \PHPUnit_Framework_TestCase
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
            array('Oro\Bundle\FlexibleEntityBundle\AttributeType\EmailCollectionType', 'integer', 'option', 'oro_flexibleentity_email_collection'),
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
