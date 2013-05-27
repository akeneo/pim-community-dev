<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'test_name';

    /**
     * @var AbstractFilter
     */
    protected $model;

    protected function setUp()
    {
        $translator = $this->getMockForAbstractClass('Symfony\Component\Translation\TranslatorInterface');

        $this->model = $this->getMockBuilder('Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter')
            ->setConstructorArgs(array($translator))
            ->setMethods(array('filter'))
            ->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @return array
     */
    public function getRenderSettingsDataProvider()
    {
        return array(
            'default' => array(
                array(),
                array(FilterType::NAME, array('show_filter' => false))
            ),
            'custom_form_type' => array(
                array('form_type' => TextFilterType::NAME),
                array(TextFilterType::NAME,
                    array('show_filter' => false)
                )
            ),
            'custom_field_type' => array(
                array('field_type' => 'text'),
                array(FilterType::NAME,
                    array('field_type' => 'text', 'show_filter' => false)
                )
            ),
            'custom_field_options' => array(
                array('field_options' => array('custom_option' => 'value')),
                array(FilterType::NAME,
                    array('field_options' => array('custom_option' => 'value'), 'show_filter' => false)
                )
            ),
            'custom_label' => array(
                array('label' => 'custom label'),
                array(FilterType::NAME,
                    array(
                        'label' => 'custom label',
                        'show_filter' => false
                    )
                )
            )
        );
    }

    /**
     * @dataProvider getRenderSettingsDataProvider
     */
    public function testGetRenderSettings($options, $expectedRenderSettings)
    {
        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertEquals($expectedRenderSettings, $this->model->getRenderSettings());
    }

    public function testGetFieldOptions()
    {
        // default value
        $this->assertEmpty($this->model->getFieldOptions());

        // predefined value
        $fieldOptions = array('key' => 'value');
        $this->model->setOption('field_options', $fieldOptions);
        $this->assertEquals($fieldOptions, $this->model->getFieldOptions());
    }

    public function testIsNullable()
    {
        // default value
        $this->assertTrue($this->model->isNullable());

        // custom value
        $this->model->setOption('nullable', false);
        $this->assertFalse($this->model->isNullable());
    }
}
