<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class FilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var FilterType
     */
    protected $type;

    protected function setUp()
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
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'text',
                    'field_options' => array(),
                    'operator_choices' => array(),
                    'operator_type' => 'choice',
                    'operator_options' => array(),
                    'show_filter' => false
                ),
                'requiredOptions' => array(
                    'field_type',
                    'field_options',
                    'operator_choices',
                    'operator_type',
                    'operator_options',
                    'show_filter'
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
            'empty data' => array(
                'bindData' => array(),
                'formData' => array('type' => null, 'value' => null),
                'viewData' => array(
                    'value' => array('type' => '', 'value' => ''),
                ),
                'customOptions' => array(
                    'operator_choices' => array()
                ),
            ),
            'empty choice' => array(
                'bindData' => array('type' => '1', 'value' => ''),
                'formData' => array('value' => null),
                'viewData' => array(
                    'value' => array('type' => '1', 'value' => ''),
                ),
                'customOptions' => array(
                    'operator_choices' => array()
                ),
            ),
            'invalid choice' => array(
                'bindData' => array('type' => '-1', 'value' => ''),
                'formData' => array('value' => null),
                'viewData' => array(
                    'value' => array('type' => '-1', 'value' => ''),
                ),
                'customOptions' => array(
                    'operator_choices' => array(
                        1 => 'Choice 1'
                    )
                ),
            ),
            'without choice' => array(
                'bindData' => array('value' => 'text'),
                'formData' => array('type' => null, 'value' => 'text'),
                'viewData' => array(
                    'value' => array('type' => '', 'value' => 'text'),
                ),
                'customOptions' => array(
                    'operator_choices' => array(
                        1 => 'Choice 1'
                    )
                ),
            ),
        );
    }
}
