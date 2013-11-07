<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class DateRangeFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateRangeFilterType
     */
    private $type;

    protected function setUp()
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getTimezone'))
            ->getMock();
        $localeSettings->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue(date_default_timezone_get()));

        $translator = $this->createMockTranslator();

        $types = array(
            new DateRangeType($localeSettings),
            new FilterType($translator)
        );

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();
        $this->type = new DateRangeFilterType($translator);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(DateRangeFilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => DateRangeType::NAME,
                    'operator_choices' => array(
                        DateRangeFilterType::TYPE_BETWEEN => 'oro.filter.form.label_date_type_between',
                        DateRangeFilterType::TYPE_NOT_BETWEEN => 'oro.filter.form.label_date_type_not_between',
                        DateRangeFilterType::TYPE_MORE_THAN => 'oro.filter.form.label_date_type_more_than',
                        DateRangeFilterType::TYPE_LESS_THAN => 'oro.filter.form.label_date_type_less_than',
                    ),
                    'widget_options' => array(),
                    'type_values' => array(
                        'between'    => DateRangeFilterType::TYPE_BETWEEN,
                        'notBetween' => DateRangeFilterType::TYPE_NOT_BETWEEN,
                        'moreThan'   => DateRangeFilterType::TYPE_MORE_THAN,
                        'lessThan'   => DateRangeFilterType::TYPE_LESS_THAN
                    )
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return array(
            'empty' => array(
                'bindData' => array(),
                'formData' => array('type' => null, 'value' => array('start' => '', 'end' => '')),
                'viewData' => array(
                    'value'          => array('type' => null, 'value' => array('start' => '', 'end' => '')),
                    'widget_options' => array('firstDay' => 1)
                ),
                'customOptions' => array(
                    'widget_options' => array('firstDay' => 1)
                )
            ),
        );
    }
}
