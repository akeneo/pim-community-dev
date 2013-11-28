<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowAttributesType;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;

class WorkflowStepTypeTest extends AbstractWorkflowAttributesTypeTestCase
{
    /**
     * @var WorkflowStepType
     */
    protected $type;

    protected function setUp()
    {
        $this->markTestIncomplete();
        parent::setUp();
        $this->type = new WorkflowStepType();
    }

    protected function tearDown()
    {
        unset($this->type);
        parent::tearDown();
    }

    protected function getExtensions()
    {
        return array(
            new PreloadedExtension(
                array(
                    WorkflowAttributesType::NAME => new WorkflowAttributesType($this->workflowRegistry),
                ),
                array()
            )
        );
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(
        $submitData,
        $formData,
        array $formOptions,
        array $childrenOptions
    ) {
        $form = $this->factory->create($this->type, null, $formOptions);

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
        $this->assertEquals($formData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        return array(
            'current_step_enabled_attribute_fields' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(
                    array(
                        'first' => 'first_string',
                        'second' => 'second_string',
                    )
                ),
                'formOptions' => array(
                    'workflow_item' => $this->createWorkflowItem(
                        $workflow = $this->createWorkflow(
                            'test_workflow',
                            array(
                                'first' => $this->createAttribute('first', 'string', 'First'),
                                'second' => $this->createAttribute('second', 'string', 'Second'),
                            ),
                            array($this->createStep('first_step'))
                        ),
                        'first_step'
                    ),
                    'workflow' => $workflow,
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                        'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                    ),
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => false),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => false),
                ),
            ),
            'not_current_step_disabled_attribute_fields' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow_item' => $this->createWorkflowItem(
                        $workflow = $this->createWorkflow(
                            'test_workflow',
                            array(
                                'first' => $this->createAttribute('first', 'string', 'First'),
                                'second' => $this->createAttribute('second', 'string', 'Second'),
                            ),
                            array($this->createStep('first_step'), $this->createStep('second_step'))
                        ),
                        'first_step'
                    ),
                    'workflow' => $workflow,
                    'step_name' => 'second_step',
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                        'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                    ),
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => true),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => true),
                ),
            ),
            'closed_workflow_item_disabled_attribute_fields' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $this->createWorkflowData(),
                'formOptions' => array(
                    'workflow_item' => $this->createWorkflowItem(
                        $workflow = $this->createWorkflow(
                            'test_workflow',
                            array(
                                'first' => $this->createAttribute('first', 'string', 'First'),
                                'second' => $this->createAttribute('second', 'string', 'Second'),
                            ),
                            array($this->createStep('first_step'))
                        ),
                        'first_step'
                    )->setClosed(true),
                    'workflow' => $workflow,
                    'step_name' => 'first_step',
                    'attribute_fields' => array(
                        'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                        'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                    ),
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
            'step_not_found' => array(
                'expectedException' => 'Symfony\Component\Form\Exception\InvalidConfigurationException',
                'expectedMessage' =>
                    'Invalid reference to unknown step "first_step" of workflow "test_workflow".',
                'options' => array(
                    'workflow_item' => $this->createWorkflowItem(
                        $workflow = $this->createWorkflow(
                            'test_workflow',
                            array(
                                'first' => $this->createAttribute('first', 'string', 'First'),
                                'second' => $this->createAttribute('second', 'string', 'Second'),
                            )
                        ),
                        'first_step'
                    ),
                    'workflow' => $workflow,
                    'step_name' => 'first_step',
                    'attribute_fields' => array(),
                ),
            ),
        );
    }
}
