<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;

class TwigTemplatePropertyTest extends \PHPUnit_Framework_TestCase
{
    const TEST_FIELD_NAME                      = 'test_field_name';
    const TEST_DB_FIELD_NAME                   = 'test_db_field_name';
    const TEST_FIELD_VALUE                     = 'test_field_value';
    const TEST_TEMPLATE_NAME                   = 'test_template_name';
    const TEST_RENDERED_TEMPLATE               = 'test_rendered template';
    const TEST_TEMPLATE_CONTEXT_PROPERTY_KEY   = 'test_template_context_property_key';
    const TEST_TEMPLATE_CONTEXT_PROPERTY_VALUE = 'test_template_context_property_value';

    /**
     * @var TwigTemplateProperty
     */
    protected $property;

    /**
     * @var FieldDescription
     */
    protected $fieldDescription;

    protected function setUp()
    {
        $this->fieldDescription = new FieldDescription();
        $this->fieldDescription->setName(self::TEST_FIELD_NAME);
        $this->fieldDescription->setFieldName(self::TEST_DB_FIELD_NAME);

        $this->property = new TwigTemplateProperty(
            $this->fieldDescription,
            self::TEST_TEMPLATE_NAME,
            array(
                self::TEST_TEMPLATE_CONTEXT_PROPERTY_KEY => self::TEST_TEMPLATE_CONTEXT_PROPERTY_VALUE
            )
        );
    }

    protected function tearDown()
    {
        unset($this->fieldDescription);
        unset($this->property);
    }

    public function testGetName()
    {
        $this->assertEquals(self::TEST_FIELD_NAME, $this->property->getName());
    }

    public function testSetEnvironment()
    {
        $environment = new \Twig_Environment();
        $this->property->setEnvironment($environment);
        $this->assertAttributeEquals($environment, 'environment', $this->property);
    }

    public function testGetValue()
    {
        // mocks
        $record = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getValue')
        );
        $record->expects($this->any())
            ->method('getValue')
            ->with(self::TEST_DB_FIELD_NAME)
            ->will($this->returnValue(self::TEST_FIELD_VALUE));

        $template = $this->getMockForAbstractClass(
            'Twig_TemplateInterface',
            array(),
            '',
            false,
            true,
            true,
            array('render')
        );

        $expectedContext = array(
            'field'      => $this->fieldDescription,
            'record'     => $record,
            'value'      => self::TEST_FIELD_VALUE,
            'properties' => array(
                self::TEST_TEMPLATE_CONTEXT_PROPERTY_KEY => self::TEST_TEMPLATE_CONTEXT_PROPERTY_VALUE
            )
        );
        $template->expects($this->once())
            ->method('render')
            ->with($expectedContext)
            ->will($this->returnValue(self::TEST_RENDERED_TEMPLATE));

        $environment = $this->getMock('Twig_Environment', array('loadTemplate'));
        $environment->expects($this->once())
            ->method('loadTemplate')
            ->with(self::TEST_TEMPLATE_NAME)
            ->will($this->returnValue($template));

        // test
        $this->property->setEnvironment($environment);
        $this->assertEquals(self::TEST_RENDERED_TEMPLATE, $this->property->getValue($record));
    }
}
