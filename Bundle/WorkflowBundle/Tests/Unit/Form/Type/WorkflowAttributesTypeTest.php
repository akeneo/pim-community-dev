<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowAttributesType;

class WorkflowAttributesTypeTest extends AbstractWorkflowAttributesTypeTestCase
{
    /**
     * @var WorkflowAttributesType
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();
        $this->type = new WorkflowAttributesType($this->workflowRegistry);
    }

    protected function tearDown()
    {
        unset($this->type);
        parent::tearDown();
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        $submitData,
        $formData,
        array $formOptions,
        array $childrenOptions,
        $sourceWorkflowData = null
    ) {
        $form = $this->factory->create($this->type, $sourceWorkflowData, $formOptions);

        $this->assertSameSize($childrenOptions, $form->all());

        foreach ($childrenOptions as $childName => $childOptions) {
            $this->assertTrue($form->has($childName));
            $childForm = $form->get($childName);
            foreach ($childOptions as $optionName => $optionValue) {
                $this->assertTrue($childForm->getConfig()->hasOption($optionName));
                $this->assertEquals($optionValue, $childForm->getConfig()->getOption($optionName));
            }
        }

        $form->submit($submitData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData(), 'Actual form data does not equal expected form data');
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return array(
            'empty_attribute_fields' => array(
                'submitData' => array(),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow' => $this->createWorkflow('test_workflow'),
                    'attribute_fields' => array()
                ),
                'childrenOptions' => array(),
            ),
            'existing_data' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(
                    array(
                        'first' => 'first_string',
                        'second' => 'second_string',
                    )
                ),
                'formOptions' => array(
                    'workflow' => $this->createWorkflow(
                        'test_workflow_with_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'attribute_fields' => array(
                        'first'  => array(
                            'form_type' => 'text',
                            'label' => 'First Custom',
                            'options' => array('required' => true)
                        ),
                        'second' => array(
                            'form_type' => 'text',
                            'options' => array('required' => false, 'label' => 'Second Custom')
                        ),
                    )
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First Custom', 'required' => true),
                    'second' => array('label' => 'Second Custom', 'required' => false),
                ),
            ),
            'partial_fields' => array(
                'submitData' => array('first' => 'first_string_modified'),
                'formData' => $this->createWorkflowData(
                    array(
                        'first' => 'first_string_modified',
                        'second' => 'second_string',
                    )
                ),
                'formOptions' => array(
                    'workflow' => $this->createWorkflow(
                        'test_workflow_with_partial_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'attribute_fields' => array(
                        'first'  => array(
                            'form_type' => 'text',
                            'label' => 'First Custom',
                            'options' => array('required' => true)
                        ),
                    )
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First Custom', 'required' => true),
                ),
                'sourceWorkflowData' => $this->createWorkflowData(
                    array(
                        'first' => 'first_string',
                        'second' => 'second_string',
                    )
                ),
            ),
            'disable_fields' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow' => $this->createWorkflow(
                        'test_workflow_with_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                        'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                    ),
                    'disable_attribute_fields' => true
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => true),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => true),
                ),
            ),
        );
    }

    /**
     * @dataProvider submitWithExceptionDataProvider
     */
    public function testSubmitWithException(
        $expectedException,
        $expectedMessage,
        array $options
    ) {
        $this->setExpectedException($expectedException, $expectedMessage);

        $form = $this->factory->create($this->type, null, $options);
        $form->submit(array());
    }

    /**
     * @return array
     */
    public function submitWithExceptionDataProvider()
    {
        return array(
            'no_workflow' => array(
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedMessage' =>
                    'One of the options must be specified: "workflow", "workflow_item", "workflow_name".',
                'options' => array('attribute_fields' => array()),
            ),
            'unknown_workflow_attribute' => array(
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedMessage' => 'Invalid reference to unknown attribute "first" of workflow "test_workflow".',
                'options' => array(
                    'workflow' => $this->createWorkflow('test_workflow'),
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true))
                    ),
                ),
            ),
            'form_type_is_not_defined' => array(
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedMessage' =>
                    'Parameter "form_type" must be defined for attribute "test" in workflow "test_workflow".',
                'options' => array(
                    'workflow' => $this->createWorkflow(
                        'test_workflow',
                        array(
                            'test' => $this->createAttribute('test', 'string', 'Test')
                        )
                    ),
                    'attribute_fields' => array(
                        'test'  => array()
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider normalizersDataProvider
     */
    public function testNormalizers(array $options, $expectedWorkflow)
    {
        $this->workflowRegistry->expects($this->once())->method('getWorkflow')
            ->with($expectedWorkflow->getName())->will($this->returnValue($expectedWorkflow));

        $this->factory->create($this->type, null, $options);
    }

    /**
     * @return array
     */
    public function normalizersDataProvider()
    {
        return array(
            'workflow_name' => array(
                'options' => array(
                    'workflow_name' => 'test_workflow',
                    'attribute_fields' => array(),
                ),
                'expectedWorkflow' => $this->createWorkflow('test_workflow'),
            ),
            'workflow_item' => array(
                'options' => array(
                    'workflow_item' => $this->createWorkflowItem($workflow = $this->createWorkflow('test_workflow')),
                    'attribute_fields' => array(),
                ),
                'expectedWorkflow' => $workflow,
            ),
        );
    }
}
