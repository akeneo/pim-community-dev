<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class ChoiceFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var ChoiceFilterType
     */
    private $type;

    protected function setUp()
    {
        $translator = $this->createMockTranslator();
        $this->formExtensions[] = new CustomFormExtension(array(new FilterType($translator)));

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
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'choice',
                    'field_options' => array('choices' => array()),
                    'operator_choices' => array(
                        ChoiceFilterType::TYPE_CONTAINS => 'oro.filter.form.label_type_contains',
                        ChoiceFilterType::TYPE_NOT_CONTAINS => 'oro.filter.form.label_type_not_contains',
                    ),
                    'populate_default' => true
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
                'formData' => array('type' => null, 'value' => null),
                'viewData' => array(
                    'value' => array('type' => null, 'value' => null),
                )
            ),
            'predefined value choice' => array(
                'bindData' => array('value' => 1),
                'formData' => array('type' => null, 'value' => 1),
                'viewData' => array(
                    'value' => array('type' => null, 'value' => 1),
                ),
                'customOptions' => array(
                    'field_options' => array(
                        'choices' => array(1 => 'One', 2 => 'Two')
                    ),
                )
            ),
            'invalid value choice' => array(
                'bindData' => array('value' => 3),
                'formData' => array('type' => null),
                'viewData' => array(
                    'value' => array('type' => null, 'value' => 3),
                ),
                'customOptions' => array(
                    'field_options' => array(
                        'choices' => array(1 => 'One')
                    ),
                )
            ),
            'multiple choices' => array(
                'bindData' => array('value' => array(1, 2)),
                'formData' => array('type' => null, 'value' => array(1, 2)),
                'viewData' => array(
                    'value' => array('type' => null, 'value' => array(1, 2)),
                ),
                'customOptions' => array(
                    'field_options' => array(
                        'multiple' => true,
                        'choices' => array(1 => 'One', 2 => 'Two', 3 => 'Three')
                    ),
                )
            ),
            'invalid multiple choices' => array(
                'bindData' => array('value' => array(3, 4)),
                'formData' => array('type' => null),
                'viewData' => array(
                    'value' => array('type' => null, 'value' => array(3, 4)),
                ),
                'customOptions' => array(
                    'field_options' => array(
                        'multiple' => true,
                        'choices' => array(1 => 'One', 2 => 'Two', 3 => 'Three')
                    ),
                )
            ),
        );
    }
}
