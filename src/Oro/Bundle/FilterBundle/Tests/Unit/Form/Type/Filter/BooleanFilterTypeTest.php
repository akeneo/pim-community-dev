<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;

class BooleanFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var BooleanFilterType
     */
    private $type;

    /**
     * @var array
     */
    protected $booleanChoices = [
        'oro.filter.form.label_type_yes' => BooleanFilterType::TYPE_YES,
        'oro.filter.form.label_type_no' => BooleanFilterType::TYPE_NO,
    ];

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();

        $types = [
            new FilterType($translator),
            new ChoiceFilterType($translator)
        ];

        $this->formExtensions[] = new CustomFormExtension($types);

        parent::setUp();
        $this->type = new BooleanFilterType($translator);
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
        $this->assertEquals(BooleanFilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_options' => ['choices' => $this->booleanChoices]
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
                'bindData' => ['value' => BooleanFilterType::TYPE_YES],
                'formData' => ['type'  => null, 'value' => BooleanFilterType::TYPE_YES],
                'viewData' => [
                    'value' => ['type' => null, 'value' => BooleanFilterType::TYPE_YES],
                ],
                'customOptions' => [
                    'field_options' => [
                        'choices' => $this->booleanChoices
                    ],
                ]
            ],
            'invalid value choice' => [
                'bindData' => ['value' => 'incorrect_value'],
                'formData' => ['type'  => null],
                'viewData' => [
                    'value' => ['type' => null, 'value' => 'incorrect_value'],
                ],
                'customOptions' => [
                    'field_options' => [
                        'choices' => $this->booleanChoices
                    ],
                ]
            ],
        ];
    }
}
