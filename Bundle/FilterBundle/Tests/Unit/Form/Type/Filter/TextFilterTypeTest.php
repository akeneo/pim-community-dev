<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class TextFilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var TextFilterType
     */
    private $type;

    protected function setUp()
    {
        $translator = $this->createMockTranslator();
        $this->formExtensions[] = new CustomFormExtension(array(new FilterType($translator)));

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
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'text',
                    'operator_choices' => array(
                        TextFilterType::TYPE_CONTAINS => 'label_type_contains',
                        TextFilterType::TYPE_NOT_CONTAINS => 'label_type_not_contains',
                        TextFilterType::TYPE_EQUAL => 'label_type_equals',
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
            'simple text' => array(
                'bindData' => array('type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'),
                'formData' => array('type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'),
                'viewData' => array(
                    'value' => array('type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'),
                ),
            ),
        );
    }
}
