<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;

class TextFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var TextFilterType
     */
    private $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();
        $this->formExtensions[] = new CustomFormExtension([new FilterType($translator)]);

        parent::setUp();
        $this->type = new TextFilterType($translator);
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
        $this->assertEquals(TextFilterType::NAME, $this->type->getName());
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
                    'operator_choices' => [
                        TextFilterType::TYPE_CONTAINS     => 'oro.filter.form.label_type_contains',
                        TextFilterType::TYPE_NOT_CONTAINS => 'oro.filter.form.label_type_not_contains',
                        TextFilterType::TYPE_EQUAL        => 'oro.filter.form.label_type_equals',
                        TextFilterType::TYPE_STARTS_WITH  => 'oro.filter.form.label_type_start_with',
                        TextFilterType::TYPE_ENDS_WITH    => 'oro.filter.form.label_type_end_with',
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
            'simple text' => [
                'bindData' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                'formData' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                'viewData' => [
                    'value' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                ],
            ],
        ];
    }
}
