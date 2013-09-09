<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\WorkflowBundle\Form\Type\OroWorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\AttributeManager;
use Oro\Bundle\WorkflowBundle\Model\StepManager;
use Oro\Bundle\WorkflowBundle\Model\TransitionManager;

class OroWorkflowStepTest extends FormIntegrationTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    /**
     * @var OroWorkflowStep
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();
        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new OroWorkflowStep($this->workflowRegistry);
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->type);
        unset($this->workflowRegistry);
    }

    /**
     * @dataProvider submitDataProvider
     * @param mixed $submitData
     * @param mixed $formData
     * @param array $formOptions
     * @param array $childrenOptions
     * @param Workflow $expectedWorkflow
     */
    public function testSubmit(
        $submitData,
        $formData,
        array $formOptions,
        array $childrenOptions,
        Workflow $expectedWorkflow
    ) {
        $this->workflowRegistry->expects($this->exactly(2))->method('getWorkflow')
            ->with($expectedWorkflow->getName())->will($this->returnValue($expectedWorkflow));

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
        // attributes and steps data fixture
        $firstAttribute = new Attribute();
        $firstAttribute->setName('first')->setType('string')->setLabel('First');
        $secondAttribute = new Attribute();
        $secondAttribute->setName('second')->setType('string')->setLabel('Second');

        $step = new Step();
        $step->setName('test_step');

        $stepWithAttributes = new Step();
        $stepWithAttributes->setName('test_step_with_attributes');
        $stepWithAttributes->setFormOptions(
            array(
                'attribute_fields' => array(
                    'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                    'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                )
            )
        );

        // workflow fixture
        $workflow = $this->createWorkflow('test_workflow');
        $workflow->getSteps()->set($step->getName(), $step);

        $workflowWithAttributes = $this->createWorkflow('test_workflow_with_attributes');
        $workflowWithAttributes->getAttributes()->set($firstAttribute->getName(), $firstAttribute);
        $workflowWithAttributes->getAttributes()->set($secondAttribute->getName(), $secondAttribute);
        $workflowWithAttributes->getSteps()->set($stepWithAttributes->getName(), $stepWithAttributes);
        $workflowWithAttributes->getSteps()->set($step->getName(), $step);

        // workflow data fixture
        $workflowData = new WorkflowData();
        $workflowData->set('first', 'first_string');
        $workflowData->set('second', 'second_string');

        return array(
            'empty data' => array(
                'submitData' => array(),
                'formData' => new WorkflowData(),
                'formOptions' => array(
                    'workflowItem' => $this->createWorkflowItem($workflow, $step->getName()),
                    'stepName' => $step->getName()
                ),
                'childrenOptions' => array(),
                'expectedWorkflow' => $workflow,
            ),
            'existing data' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => $workflowData,
                'formOptions' => array(
                    'workflowItem' => $this->createWorkflowItem(
                        $workflowWithAttributes, $stepWithAttributes->getName()
                    ),
                    'stepName' => $stepWithAttributes->getName()
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true),
                    'second' => array('label' => 'Second', 'required' => false),
                ),
                'expectedWorkflow' => $workflowWithAttributes,
            ),
            'disable fields on closed workflow' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => new WorkflowData(), // empty workflow data
                'formOptions' => array(
                    'workflowItem' => $this->createWorkflowItem(
                        $workflowWithAttributes, $stepWithAttributes->getName()
                    )->setClosed(true),
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => true),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => true),
                ),
                'expectedWorkflow' => $workflowWithAttributes,
            ),
            'disable fields on not current step' => array(
                'submitData' => array('first' => 'first_string', 'second' => 'second_string'),
                'formData' => new WorkflowData(), // empty workflow data
                'formOptions' => array(
                    'workflowItem' => $this->createWorkflowItem(
                        $workflowWithAttributes, $step->getName()
                    )->setClosed(true),
                    'stepName' => $stepWithAttributes->getName()
                ),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true, 'disabled' => true),
                    'second' => array('label' => 'Second', 'required' => false, 'disabled' => true),
                ),
                'expectedWorkflow' => $workflowWithAttributes,
            ),
        );
    }

    /**
     * @dataProvider submitWithExceptionDataProvider
     * @param $expectedException
     * @param $expectedMessage
     * @param array $options
     * @param Workflow $expectedWorkflow
     */
    public function testSubmitWithException(
        $expectedException,
        $expectedMessage,
        array $options,
        Workflow $expectedWorkflow = null
    ) {
        if ($expectedWorkflow) {
            $this->workflowRegistry->expects($this->any())->method('getWorkflow')
                ->with($expectedWorkflow->getName())->will($this->returnValue($expectedWorkflow));
        }

        $this->setExpectedException($expectedException, $expectedMessage);

        $form = $this->factory->create($this->type, null, $options);
        $form->submit(array());
    }

    /**
     * @return array
     */
    public function submitWithExceptionDataProvider()
    {
        $step = new Step();
        $step->setName('test_step');
        $workflow = $this->createWorkflow('test_workflow');

        $stepUnknownAttribute = new Step();
        $stepUnknownAttribute->setName('test_step');
        $stepUnknownAttribute->setFormOptions(
            array(
                'attribute_fields' => array(
                    'unknown_attribute' => array('data')
                )
            )
        );
        $workflowUnknownAttribute = $this->createWorkflow('test_workflow');
        $workflowUnknownAttribute->getSteps()->set($stepUnknownAttribute->getName(), $stepUnknownAttribute);

        $attribute = new Attribute();
        $attribute->setName('test_attribute')
            ->setType('string')
            ->setLabel('First');

        $stepNoFormType = new Step();
        $stepNoFormType->setName('test_step');
        $stepNoFormType->setFormOptions(
            array(
                'attribute_fields' => array(
                    'test_attribute' => array('label' => 'Test')
                )
            )
        );
        $workflowNoFormType = $this->createWorkflow('test_workflow');
        $workflowNoFormType->getAttributes()->set($attribute->getName(), $attribute);
        $workflowNoFormType->getSteps()->set($stepNoFormType->getName(), $stepNoFormType);

        return array(
            'not a workflow instance' => array(
                'expectedException' => '\Symfony\Component\Form\Exception\UnexpectedTypeException',
                'expectedMessage'
                    => 'Expected argument of type "Oro\Bundle\WorkflowBundle\Entity\WorkflowItem", "array" given',
                'options' => array(
                    'workflowItem' => array('data'),
                    'stepName'     => $step->getName()
                )
            ),
            'unknown step' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\UnknownStepException',
                'expectedMessage'   => 'Step "test_step" not found',
                'options' => array(
                    'workflowItem' => $this->createWorkflowItem($workflow),
                    'stepName' => $step->getName()
                ),
                'expectedWorkflow' => $workflow
            ),
            'form type is not defined' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedMessage'   => 'Parameter "form_type" must be defined for attribute ' .
                    '"test_attribute" in workflow "test_workflow"',
                'options' => array(
                    'workflowItem' => $this->createWorkflowItem($workflowNoFormType),
                    'stepName'     => $stepNoFormType->getName()
                ),
                'expectedWorkflow' => $workflowNoFormType
            ),
        );
    }

    /**
     * @param string $workflowName
     * @return Workflow
     */
    protected function createWorkflow($workflowName)
    {
        $workflow = new Workflow(
            new StepManager(),
            new AttributeManager(),
            new TransitionManager()
        );

        $workflow->setName($workflowName);

        return $workflow;
    }

    /**
     * @param Workflow $workflow
     * @param string $currentStepName
     * @return WorkflowItem
     */
    protected function createWorkflowItem(Workflow $workflow, $currentStepName = null)
    {
        $result = new WorkflowItem();
        $result->setCurrentStepName($currentStepName);
        $result->setWorkflowName($workflow->getName());
        return $result;
    }
}
