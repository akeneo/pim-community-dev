<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

abstract class AbstractWorkflowAttributesTypeTestCase extends FormIntegrationTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    protected function setUp()
    {
        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->workflowRegistry);
        parent::tearDown();
    }

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
}
