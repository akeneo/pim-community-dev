<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var ChoiceFilterType
     */
    private $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();
        $this->formExtensions[] = new CustomFormExtension([new FilterType($translator)]);

        parent::setUp();
        $this->type = new ChoiceFilterType($translator);
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
        $this->assertEquals(ChoiceFilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'       => ChoiceType::class,
                    'field_options'    => ['choices' => []],
                    'operator_choices' => [
                        ChoiceFilterType::TYPE_CONTAINS     => 'oro.filter.form.label_type_contains',
                        ChoiceFilterType::TYPE_NOT_CONTAINS => 'oro.filter.form.label_type_not_contains',
                    ],
                    'populate_default' => true
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
                'formData' => ['type' => null, 'value' => null],
                'viewData' => [
                    'value' => ['type' => null, 'value' => null],
                ]
            ],
            'predefined value choice' => [
                'bindData' => ['value' => 1],
                'formData' => ['type'  => null, 'value' => 1],
                'viewData' => [
                    'value' => ['type' => null, 'value' => 1],
                ],
                'customOptions' => [
                    'field_options' => [
                        'choices' => [1 => 'One', 2 => 'Two']
                    ],
                ]
            ],
            'invalid value choice' => [
                'bindData' => ['value' => 3],
                'formData' => ['type'  => null],
                'viewData' => [
                    'value' => ['type' => null, 'value' => 3],
                ],
                'customOptions' => [
                    'field_options' => [
                        'choices' => [1 => 'One']
                    ],
                ]
            ],
            'multiple choices' => [
                'bindData' => ['value' => [1, 2]],
                'formData' => ['type'  => null, 'value' => [1, 2]],
                'viewData' => [
                    'value' => ['type' => null, 'value' => [1, 2]],
                ],
                'customOptions' => [
                    'field_options' => [
                        'multiple' => true,
                        'choices'  => [1 => 'One', 2 => 'Two', 3 => 'Three']
                    ],
                ]
            ],
            'invalid multiple choices' => [
                'bindData' => ['value' => [3, 4]],
                'formData' => ['type'  => null],
                'viewData' => [
                    'value' => ['type' => null, 'value' => [3, 4]],
                ],
                'customOptions' => [
                    'field_options' => [
                        'multiple' => true,
                        'choices'  => [1 => 'One', 2 => 'Two', 3 => 'Three']
                    ],
                ]
            ],
        ];
    }
}
