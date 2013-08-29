<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\WorkflowBundle\Form\Type\OroWorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;
use Oro\Bundle\WorkflowBundle\Model\AttributeManager;
use Oro\Bundle\WorkflowBundle\Model\StepManager;
use Oro\Bundle\WorkflowBundle\Model\TransitionManager;

class OroWorkflowStepTest extends FormIntegrationTestCase
{
    /**
     * @var OroWorkflowStep
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();
        $this->type = new OroWorkflowStep();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->type);
    }

    /**
     * @return EntityBinder
     */
    protected function getEntityBinder()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider submitDataProvider
     * @param mixed $submitData
     * @param mixed $formData
     * @param array $formOptions
     * @param array $childrenOptions
     */
    public function testSubmit($submitData, $formData, array $formOptions, array $childrenOptions)
    {
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
        $stepWithAttributes->setName('test_step');
        $stepWithAttributes->setFormOptions(
            array(
                'attribute_fields' => array(
                    'first'  => array('form_type' => 'text', 'options' => array('required' => true)),
                    'second' => array('form_type' => 'text', 'options' => array('required' => false)),
                )
            )
        );

        // workflow fixture
        $workflow = $this->createWorkflow();
        $workflow->getSteps()->set($step->getName(), $step);

        $workflowWithAttributes = $this->createWorkflow();
        $workflowWithAttributes->getAttributes()->set($firstAttribute->getName(), $firstAttribute);
        $workflowWithAttributes->getAttributes()->set($secondAttribute->getName(), $secondAttribute);
        $workflowWithAttributes->getSteps()->set($stepWithAttributes->getName(), $stepWithAttributes);

        // workflow data fixture
        $workflowData = new WorkflowData();
        $workflowData->set('first', 'first_string');
        $workflowData->set('second', 'second_string');

        return array(
            'empty data' => array(
                'submitData'      => array(),
                'formData'        => new WorkflowData(),
                'formOptions'     => array('workflow' => $workflow, 'step' => $step),
                'childrenOptions' => array(),
            ),
            'existing data' => array(
                'submitData'      => array('first' => 'first_string', 'second' => 'second_string'),
                'formData'        => $workflowData,
                'formOptions'     => array('workflow' => $workflowWithAttributes, 'step' => $stepWithAttributes),
                'childrenOptions' => array(
                    'first'  => array('label' => 'First', 'required' => true),
                    'second' => array('label' => 'Second', 'required' => false),
                ),
            ),
        );
    }

    /**
     * @dataProvider submitWithExceptionDataProvider
     * @param $expectedException
     * @param $expectedMessage
     * @param array $options
     */
    public function testSubmitWithException($expectedException, $expectedMessage, array $options)
    {
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
        $workflow = $this->createWorkflow();

        $stepUnknownAttribute = new Step();
        $stepUnknownAttribute->setName('test_step');
        $stepUnknownAttribute->setFormOptions(
            array(
                'attribute_fields' => array(
                    'unknown_attribute' => array('data')
                )
            )
        );
        $workflowUnknownAttribute = $this->createWorkflow();
        $workflowUnknownAttribute->setName('test_workflow');
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
        $workflowNoFormType = $this->createWorkflow();
        $workflowNoFormType->setName('test_workflow');
        $workflowNoFormType->getAttributes()->set($attribute->getName(), $attribute);
        $workflowNoFormType->getSteps()->set($stepNoFormType->getName(), $stepNoFormType);

        return array(
            'not a workflow instance' => array(
                'expectedException' => '\Symfony\Component\Form\Exception\UnexpectedTypeException',
                'expectedMessage'
                    => 'Expected argument of type "Oro\Bundle\WorkflowBundle\Model\Workflow", "array" given',
                'options' => array(
                    'workflow' => array('data'),
                    'step'     => $step
                )
            ),
            'not a step instance' => array(
                'expectedException' => '\Symfony\Component\Form\Exception\UnexpectedTypeException',
                'expectedMessage'
                    => 'Expected argument of type "Oro\Bundle\WorkflowBundle\Model\Step", "array" given',
                'options' => array(
                    'workflow' => $workflow,
                    'step'     => array('data')
                )
            ),
            'unknown step' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\UnknownStepException',
                'expectedMessage'   => 'Step "test_step" not found',
                'options' => array(
                    'workflow' => $workflow,
                    'step'     => $step
                )
            ),
            'unknown attribute in workflow' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException',
                'expectedMessage'   => 'Unknown attribute "unknown_attribute" in workflow "test_workflow"',
                'options' => array(
                    'workflow' => $workflowUnknownAttribute,
                    'step'     => $stepUnknownAttribute
                )
            ),
            'form type is not defined' => array(
                'expectedException' => '\Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException',
                'expectedMessage'   => 'Parameter "form_type" must be defined for attribute ' .
                    '"test_attribute" in workflow "test_workflow"',
                'options' => array(
                    'workflow' => $workflowNoFormType,
                    'step'     => $stepNoFormType
                )
            ),
        );
    }

    /**
     * @return Workflow
     */
    protected function createWorkflow()
    {
        return new Workflow(
            new StepManager(),
            new AttributeManager(),
            new TransitionManager()
        );
    }
}
