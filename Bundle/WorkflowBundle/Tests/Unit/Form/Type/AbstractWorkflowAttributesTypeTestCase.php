<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\WorkflowBundle\Form\EventListener\DefaultValuesListener;
use Oro\Bundle\WorkflowBundle\Form\EventListener\InitActionsListener;
use Oro\Bundle\WorkflowBundle\Form\EventListener\RequiredAttributesListener;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowAttributesType;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

abstract class AbstractWorkflowAttributesTypeTestCase extends FormIntegrationTestCase
{
    /**
     * @param string $workflowName
     * @param array $attributes
     * @param array $steps
     * @return Workflow
     */
    protected function createWorkflow($workflowName, array $attributes = array(), array $steps = array())
    {
        $workflow = new Workflow();

        $workflow->setName($workflowName);

        foreach ($attributes as $name => $attribute) {
            $workflow->getAttributeManager()->getAttributes()->set($name, $attribute);
        }

        $workflow->getStepManager()->setSteps($steps);

        return $workflow;
    }

    /**
     * @param array $data
     * @return WorkflowData
     */
    protected function createWorkflowData(array $data = array())
    {
        $result = new WorkflowData();
        foreach ($data as $name => $value) {
            $result->set($name, $value);
        }
        return $result;
    }

    /**
     * @param string|null $name
     * @param string|null $type
     * @param string|null $label
     * @return Attribute
     */
    protected function createAttribute($name = null, $type = null, $label = null)
    {
        $result = new Attribute();
        $result->setName($name);
        $result->setType($type);
        $result->setLabel($label);
        return $result;
    }

    /**
     * @param string|null $name
     * @return Step
     */
    protected function createStep($name = null)
    {
        $result = new Step();
        $result->setName($name);
        return $result;
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

    protected function createWorkflowAttributesType(
        WorkflowRegistry $workflowRegistry = null,
        DefaultValuesListener $defaultValuesListener = null,
        InitActionsListener $initActionListener = null,
        RequiredAttributesListener $requiredAttributesListener = null
    ) {
        if (!$workflowRegistry) {
            $workflowRegistry = $this->createWorkflowRegistryMock();
        }
        if (!$defaultValuesListener) {
            $defaultValuesListener = $this->createDefaultValuesListenerMock();
        }
        if (!$initActionListener) {
            $initActionListener = $this->createInitActionsListenerMock();
        }
        if (!$requiredAttributesListener) {
            $requiredAttributesListener = $this->createRequiredAttributesListenerMock();
        }

        return new WorkflowAttributesType(
            $workflowRegistry,
            $defaultValuesListener,
            $initActionListener,
            $requiredAttributesListener
        );
    }

    protected function createWorkflowRegistryMock()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getWorkflow'))
            ->getMock();
    }

    protected function createDefaultValuesListenerMock()
    {
        return$this->getMockBuilder('Oro\Bundle\WorkflowBundle\Form\EventListener\DefaultValuesListener')
            ->disableOriginalConstructor()
            ->setMethods(array('initialize', 'setDefaultValues'))
            ->getMock();
    }

    protected function createInitActionsListenerMock()
    {
        return$this->getMockBuilder('Oro\Bundle\WorkflowBundle\Form\EventListener\InitActionsListener')
            ->disableOriginalConstructor()
            ->setMethods(array('initialize', 'executeInitAction'))
            ->getMock();
    }

    protected function createRequiredAttributesListenerMock()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Form\EventListener\RequiredAttributesListener')
            ->disableOriginalConstructor()
            ->setMethods(array('initialize', 'onPreSetData', 'onSubmit'))
            ->getMock();
    }
}
