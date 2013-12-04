<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowAttributesType;

class WorkflowAttributesTypeTest extends AbstractWorkflowAttributesTypeTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultValuesListener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $initActionListener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requiredAttributesListener;

    /**
     * @var WorkflowAttributesType
     */
    protected $type;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    protected function setUp()
    {
        parent::setUp();

        $this->workflowRegistry = $this->createWorkflowRegistryMock();
        $this->defaultValuesListener = $this->createDefaultValuesListenerMock();
        $this->initActionListener = $this->createInitActionsListenerMock();
        $this->requiredAttributesListener = $this->createRequiredAttributesListenerMock();

        $this->type = $this->createWorkflowAttributesType(
            $this->workflowRegistry,
            $this->defaultValuesListener,
            $this->initActionListener,
            $this->requiredAttributesListener
        );
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
        // Check default values listener is subscribed or not subscribed
        if (!empty($formOptions['attribute_default_values'])) {
            $this->defaultValuesListener->expects($this->once())
                ->method('initialize')
                ->with(
                    $formOptions['workflow_item'],
                    isset($formOptions['attribute_default_values']) ? $formOptions['attribute_default_values'] : array()
                );
            $this->defaultValuesListener->expects($this->once())
                ->method('setDefaultValues')
                ->with($this->isInstanceOf('Symfony\Component\Form\FormEvent'));
        } else {
            $this->defaultValuesListener->expects($this->never())->method($this->anything());
        }

        // Check init action listener is subscribed or not subscribed
        if (!empty($formOptions['init_actions'])) {
            $this->initActionListener->expects($this->once())
                ->method('initialize')
                ->with(
                    $formOptions['workflow_item'],
                    $formOptions['init_actions']
                );
            $this->initActionListener->expects($this->once())
                ->method('initActionListener')
                ->with($this->isInstanceOf('Symfony\Component\Form\FormEvent'));
        } else {
            $this->initActionListener->expects($this->never())->method($this->anything());
        }

        // Check required attributes listener is subscribed or not subscribed
        if (!empty($formOptions['attribute_fields'])) {
            $this->requiredAttributesListener->expects($this->once())
                ->method('initialize')
                ->with(array_keys($formOptions['attribute_fields']));
            $this->requiredAttributesListener->expects($this->once())
                ->method('onPreSetData')
                ->with($this->isInstanceOf('Symfony\Component\Form\FormEvent'));
            $this->requiredAttributesListener->expects($this->once())
                ->method('onSubmit')
                ->with($this->isInstanceOf('Symfony\Component\Form\FormEvent'));
        } else {
            $this->requiredAttributesListener->expects($this->never())->method($this->anything());
        }

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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function submitDataProvider()
    {
        return array(
            'empty_attribute_fields' => array(
                'submitData' => array(),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow_item' => $this->createWorkflowItem($workflow = $this->createWorkflow('test_workflow')),
                    'workflow' => $workflow,
                    'attribute_fields' => array()
                ),
                'childrenOptions' => array()
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
                    'workflow' => $workflow = $this->createWorkflow(
                        'test_workflow_with_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'workflow_item' => $this->createWorkflowItem($workflow),
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
                )
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
                    'workflow' => $workflow = $this->createWorkflow(
                        'test_workflow_with_partial_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'workflow_item' => $this->createWorkflowItem($workflow),
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
                )
            ),
            'disable_fields' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow' => $workflow = $this->createWorkflow(
                        'test_workflow_with_attributes',
                        array(
                            'first' => $this->createAttribute('first', 'string', 'First'),
                            'second' => $this->createAttribute('second', 'string', 'Second'),
                        )
                    ),
                    'workflow_item' => $this->createWorkflowItem($workflow),
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                        'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                    ),
                    'disable_attribute_fields' => true
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => true),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => true),
                )
            )
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
            'no_workflow_item' => array(
                'expectedException' => 'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                'expectedMessage' =>
                    'The required option "workflow_item" is  missing.',
                'options' => array(),
            ),
            'unknown_workflow_attribute' => array(
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedMessage' => 'Invalid reference to unknown attribute "first" of workflow "test_workflow".',
                'options' => array(
                    'workflow' => $workflow = $this->createWorkflow('test_workflow'),
                    'workflow_item' => $this->createWorkflowItem($workflow),
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
                    'workflow_item' => $this->createWorkflowItem($workflow),
                    'attribute_fields' => array(
                        'test'  => array()
                    ),
                )
            ),
        );
    }

    public function testNormalizers()
    {
        $expectedWorkflow = $this->createWorkflow('test_workflow');
        $options = array(
            'workflow_item' => $this->createWorkflowItem($expectedWorkflow),
            'attribute_fields' => array(),
        );

        $this->workflowRegistry->expects($this->once())->method('getWorkflow')
            ->with($expectedWorkflow->getName())->will($this->returnValue($expectedWorkflow));

        $this->factory->create($this->type, null, $options);
    }
}
