<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\PimFilterBundle\Form\Type\DateRangeType;

class DateRangeFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateRangeFilterType
     */
    private $type;

    protected function setUp(): void
    {
        $localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(['getTimezone'])
            ->getMock();
        $localeSettings->expects($this->any())
            ->method('getTimezone')
            ->will($this->returnValue(date_default_timezone_get()));

        $translator = $this->createMockTranslator();

        $types = [
            new DateRangeType($localeSettings),
            new FilterType($translator)
        ];

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
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'       => DateRangeType::class,
                    'operator_choices' => [
                        DateRangeFilterType::TYPE_BETWEEN     => 'oro.filter.form.label_date_type_between',
                        DateRangeFilterType::TYPE_NOT_BETWEEN => 'oro.filter.form.label_date_type_not_between',
                        DateRangeFilterType::TYPE_MORE_THAN   => 'oro.filter.form.label_date_type_more_than',
                        DateRangeFilterType::TYPE_LESS_THAN   => 'oro.filter.form.label_date_type_less_than',
                    ],
                    'widget_options' => [],
                    'type_values'    => [
                        'between'    => DateRangeFilterType::TYPE_BETWEEN,
                        'notBetween' => DateRangeFilterType::TYPE_NOT_BETWEEN,
                        'moreThan'   => DateRangeFilterType::TYPE_MORE_THAN,
                        'lessThan'   => DateRangeFilterType::TYPE_LESS_THAN
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return [
            'empty' => [
                'bindData' => [],
                'formData' => ['type' => null, 'value' => ['start' => '', 'end' => '']],
                'viewData' => [
                    'value'          => ['type'     => null, 'value' => ['start' => '', 'end' => '']],
                    'widget_options' => ['firstDay' => 1]
                ],
                'customOptions' => [
                    'widget_options' => ['firstDay' => 1]
                ]
            ],
        ];
    }
}
