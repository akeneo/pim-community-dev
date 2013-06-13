<?php

namespace Pim\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryFilterTypeTest extends AbstractTypeTestCase
{

    /**
     * @var CategoryFilterType
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $translator = $this->createMockTranslator();
        $this->type = new CategoryFilterType($translator);
        $this->factory->addType(new FilterType($translator));
        $this->factory->addType(new ChoiceFilterType($translator));
    }

    /**
     * {@inheritdoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $this->assertEquals(CategoryFilterType::NAME, $this->type->getName());
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
                        CategoryFilterType::TYPE_CONTAINS => 'label_type_contains',
                        CategoryFilterType::TYPE_NOT_CONTAINS => 'label_type_not_contains',
                        CategoryFilterType::TYPE_CLASSIFIED => 'label_type_contains',
                        CategoryFilterType::TYPE_UNCLASSIFIED => 'label_type_contains'
                    ),
                    'type_values' => array(
                        'contains' => CategoryFilterType::TYPE_CONTAINS,
                        'notContains' => CategoryFilterType::TYPE_NOT_CONTAINS,
                        'classified' => CategoryFilterType::TYPE_CLASSIFIED,
                        'unclassified' => CategoryFilterType::TYPE_UNCLASSIFIED
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
                    )
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
                    )
                )
            )
        );
    }
}
