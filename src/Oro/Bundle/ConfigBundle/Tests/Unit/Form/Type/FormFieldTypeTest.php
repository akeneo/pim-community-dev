<?php

namespace ConfigBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\Tree\FieldNodeDefinition;
use Oro\Bundle\ConfigBundle\Form\Type\FormFieldType;
use Symfony\Component\Form\Test\TypeTestCase;

class FormFieldTypeTest extends TypeTestCase
{
    const TEST_LABEL = 'label';

    /** @var FormFieldType */
    protected $formType;

    public function setUp()
    {
        parent::setUp();
        $this->formType = new FormFieldType();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->formType);
    }

    /**
     * @dataProvider buildFormOptionsProvider
     *
     * @param array  $options
     * @param string $expectedType
     * @param array  $expectedOptions
     */
    public function testBuildForm($options, $expectedType, array $expectedOptions)
    {
        $form = $this->factory->create($this->formType, [], $options);

        $this->assertTrue($form->has('value'));
        $this->assertTrue($form->has('use_parent_scope_value'));

        $this->assertEquals($expectedType, $form->get('value')->getConfig()->getType()->getName());

        foreach ($expectedOptions as $option => $value) {
            $this->assertEquals($value, $form->get('value')->getConfig()->getOption($option));
        }
    }

    /**
     * @return array
     */
    public function buildFormOptionsProvider()
    {
        return [
            'target field options empty'                => [
                'options'         => [],
                'expectedType'    => 'text',
                'expectedOptions' => []
            ],
            'target field options from array'           => [
                'options'         => [
                    'target_field' => [
                        'type'    => 'choice',
                        'options' => ['label' => self::TEST_LABEL]
                    ]
                ],
                'expectedType'    => 'choice',
                'expectedOptions' => ['label' => self::TEST_LABEL]
            ],
            'target field options from FieldDefinition' => [
                'options'         => [
                    'target_field' => new FieldNodeDefinition(
                        'test_field_name',
                        [
                            'type'    => 'choice',
                            'options' => [
                                'label' => self::TEST_LABEL
                            ]
                        ]
                    )
                ],
                'expectedType'    => 'choice',
                'expectedOptions' => ['label' => self::TEST_LABEL]
            ],
        ];
    }

    public function testGetName()
    {
        $this->assertEquals('oro_config_form_field_type', $this->formType->getName());
    }
}
