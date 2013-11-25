<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;

use Oro\Bundle\FormBundle\Form\Extension\JsValidationExtension;

class JsValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $constraintsProvider;

    /**
     * @var JsValidationExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->constraintsProvider = $this->getMockBuilder(
            'Oro\Bundle\FormBundle\Form\Extension\JsValidation\ConstraintsProvider'
        )->disableOriginalConstructor()->getMock();
        $this->extension = new JsValidationExtension($this->constraintsProvider);
    }

    /**
     * @dataProvider finishViewAddOptionalGroupAttributeDataProvider
     */
    public function testFinishViewAddOptionalGroupAttribute(
        FormView $view,
        FormInterface $form,
        array $options,
        array $expectedAttributes
    ) {
        $this->constraintsProvider->expects($this->once())
            ->method('getFormConstraints')
            ->will($this->returnValue(array()));

        $this->extension->finishView($view, $form, $options);

        $this->assertEquals($expectedAttributes, $view->vars['attr']);
    }

    public function finishViewAddOptionalGroupAttributeDataProvider()
    {
        return array(
            'not_optional_group_without_children' => array(
                'view' => $this->createView(
                    array(),
                    array(),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(),
                'expectedAttributes' => array()
            ),
            'not_optional_group_without_parent' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView())
                ),
                'form' => $this->createForm(),
                'options' => array(),
                'expectedAttributes' => array()
            ),
            'not_optional_group_with_choices' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView()),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(
                    'choices' => array('1' => 'Yes', '0' => 'No')
                ),
                'expectedAttributes' => array()
            ),
            'not_optional_group_required' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView()),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(
                    'required' => true
                ),
                'expectedAttributes' => array()
            ),
            'not_optional_group_required_and_not_inherit_data' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView()),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(
                    'required' => true,
                    'inherit_data' => false
                ),
                'expectedAttributes' => array()
            ),
            'optional_group' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView()),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(
                    'required' => false
                ),
                'expectedAttributes' => array(
                    'data-validation-optional-group' => null,
                )
            ),
            'optional_group_required_but_inherit_data' => array(
                'view' => $this->createView(
                    array(),
                    array($this->createView()),
                    $this->createView()
                ),
                'form' => $this->createForm(),
                'options' => array(
                    'required' => true,
                    'inherit_data' => true
                ),
                'expectedAttributes' => array(
                    'data-validation-optional-group' => null,
                )
            ),
        );
    }

    /**
     * @dataProvider finishViewAddDataValidationAttributeDataProvider
     */
    public function testFinishViewAddDataValidationAttribute(
        FormView $view,
        FormInterface $form,
        array $expectedConstraints,
        array $expectedAttributes
    ) {
        $this->constraintsProvider->expects($this->once())
            ->method('getFormConstraints')
            ->will($this->returnValue($expectedConstraints));

        $this->extension->finishView($view, $form, array());

        $this->assertEquals($expectedAttributes, $view->vars['attr']);
    }

    /**
     * @return array
     */
    public function finishViewAddDataValidationAttributeDataProvider()
    {
        $constraintWithNestedData = new Constraints\NotNull();
        $constraintWithNestedData->message = array(
            'object' => new \stdClass(),
            'array' => array(
                'object' => new \stdClass(),
                'integer' => 2,
            ),
            'integer' => 1,
        );

        $constraintWithCustomName = $this->getMock('Symfony\Component\Validator\Constraint');
        $constraintWithCustomName->foo = 1;

        return array(
            'set_nested_data' => array(
                'view' => $this->createView(),
                'form' => $this->createForm(),
                'expectedConstraints' => array($constraintWithNestedData),
                'expectedAttributes' => array(
                    'data-validation' => '{"NotNull":{"message":{"array":{"integer":2},"integer":1}}}'
                )
            ),
            'set_custom_name' => array(
                'view' => $this->createView(),
                'form' => $this->createForm(),
                'expectedConstraints' => array($constraintWithCustomName),
                'expectedAttributes' => array(
                    'data-validation' => '{"' . get_class($constraintWithCustomName) . '":{}}'
                )
            ),
            'set_default' => array(
                'view' => $this->createView(),
                'form' => $this->createForm(),
                'expectedConstraints' => array(new Constraints\NotBlank()),
                'expectedAttributes' => array(
                    'data-validation' => '{"NotBlank":{"message":"This value should not be blank."}}'
                )
            ),
            'merge_with_array' => array(
                'view' => $this->createView(
                    array(
                        'attr' => array(
                            'data-validation' => array(
                                'NotNull' => array('NotNull' => array('message' => 'This value should not be null.'))
                            )
                        )
                    )
                ),
                'form' => $this->createForm(),
                'expectedConstraints' => array(new Constraints\NotBlank()),
                'expectedAttributes' => array(
                    'data-validation' =>
                        '{' .
                        '"NotNull":{"NotNull":{"message":"This value should not be null."}},' .
                        '"NotBlank":{"message":"This value should not be blank."}'.
                        '}'
                )
            ),
            'merge_with_json_string' => array(
                'view' => $this->createView(
                    array(
                        'attr' => array(
                            'data-validation' => '{"NotNull":{"message":"This value should not be null."}}'
                        )
                    )
                ),
                'form' => $this->createForm(),
                'expectedConstraints' => array(new Constraints\NotBlank()),
                'expectedAttributes' => array(
                    'data-validation' =>
                        '{' .
                        '"NotNull":{"message":"This value should not be null."},' .
                        '"NotBlank":{"message":"This value should not be blank."}'.
                        '}'
                )
            ),
            'override_invalid_value' => array(
                'view' => $this->createView(
                    array(
                        'attr' => array(
                            'data-validation' => '{"NotNull":}'
                        )
                    )
                ),
                'form' => $this->createForm(),
                'expectedConstraints' => array(
                    new Constraints\NotBlank()
                ),
                'expectedAttributes' => array(
                    'data-validation' =>
                        '{"NotBlank":{"message":"This value should not be blank."}}'
                )
            ),
        );
    }

    /**
     * @param array $vars
     * @param array $children
     * @param FormView $parent
     * @return FormView
     */
    protected function createView(array $vars = array(), array $children = array(), FormView $parent = null)
    {
        $result = new FormView();
        $result->vars = array_merge_recursive($result->vars, $vars);
        $result->children = $children;
        $result->parent = $parent;
        return $result;
    }

    /**
     * @return FormInterface
     */
    protected function createForm()
    {
        return $this->getMock('Symfony\Component\Form\Test\FormInterface');
    }
}
