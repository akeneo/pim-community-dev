<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\PimFilterBundle\Form\Type\DateRangeType;
use Oro\Bundle\PimFilterBundle\Form\Type\DateTimeRangeType;

class DateTimeRangeFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var DateTimeRangeFilterType
     */
    private $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();

        $types = [
            new FilterType($translator),
            new DateRangeType(),
            new DateTimeRangeType(),
            new DateRangeFilterType($translator)
        ];

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();
        $this->type = new DateTimeRangeFilterType();
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
        $this->assertEquals(DateTimeRangeFilterType::NAME, $this->type->getBlockPrefix());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type' => DateTimeRangeType::class,
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
