<?php

namespace Pim\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter\ChoiceFilterTypeTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryFilterTypeTest extends ChoiceFilterTypeTest
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
        $this->markTestSkipped('Due to Symfony 2.3 Upgrade, cf https://github.com/symfony/symfony/blob/master/UPGRADE-2.1.md');
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
        $this->assertEquals(ChoiceFilterType::NAME, $this->type->getParent());
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
}
