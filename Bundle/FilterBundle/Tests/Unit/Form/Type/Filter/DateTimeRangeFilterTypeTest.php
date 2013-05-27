<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\FilterBundle\Form\Type\DateTimeRangeType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class DateTimeRangeFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateTimeRangeFilterType
     */
    private $type;

    protected function setUp()
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new DateTimeRangeFilterType($translator);
        $this->factory->addType(new FilterType($translator));
        $this->factory->addType(new DateRangeType($translator));
        $this->factory->addType(new DateTimeRangeType($translator));
        $this->factory->addType(new DateRangeFilterType($translator));
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
        $this->assertEquals(DateTimeRangeFilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => DateTimeRangeType::NAME,
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
                    'widget_options' => array('dateFormat' => 'mm/dd/yy', 'timeFormat' => 'hh:mm', 'firstDay' => 1)
                ),
                'customOptions' => array(
                    'widget_options' => array('firstDay' => 1, 'timeFormat' => 'hh:mm')
                )
            ),
        );
    }
}
