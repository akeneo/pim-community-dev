<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;

class FilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var FilterType
     */
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new FilterType($translator);
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
        $this->assertEquals(FilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'       => 'text',
                    'field_options'    => [],
                    'operator_choices' => [],
                    'operator_type'    => 'choice',
                    'operator_options' => [],
                    'show_filter'      => false
                ],
                'requiredOptions' => [
                    'field_type',
                    'field_options',
                    'operator_choices',
                    'operator_type',
                    'operator_options',
                    'show_filter'
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
            'empty data' => [
                'bindData' => [],
                'formData' => ['type' => null, 'value' => null],
                'viewData' => [
                    'value' => ['type' => '', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => []
                ],
            ],
            'empty choice' => [
                'bindData' => ['type'  => '1', 'value' => ''],
                'formData' => ['value' => null],
                'viewData' => [
                    'value' => ['type' => '1', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => []
                ],
            ],
            'invalid choice' => [
                'bindData' => ['type'  => '-1', 'value' => ''],
                'formData' => ['value' => null],
                'viewData' => [
                    'value' => ['type' => '-1', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => [
                        1 => 'Choice 1'
                    ]
                ],
            ],
            'without choice' => [
                'bindData' => ['value' => 'text'],
                'formData' => ['type'  => null, 'value' => 'text'],
                'viewData' => [
                    'value' => ['type' => '', 'value' => 'text'],
                ],
                'customOptions' => [
                    'operator_choices' => [
                        1 => 'Choice 1'
                    ]
                ],
            ],
        ];
    }
}
