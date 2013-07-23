<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Transition;

class Workflow
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var Collection
     */
    protected $steps;

    /**
     * @var Collection
     */
    protected $transitions;

    /**
     * @var Step
     */
    protected $startStep;

    /**
     * @var string
     */
    protected $managedEntityType;

    public function __construct()
    {
        $this->transitions = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->enabled = true;
    }

    /**
     * Set enabled.
     *
     * @param boolean $enabled
     * @return Workflow
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool)$enabled;
        return $this;
    }

    /**
     * Is workflow enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set managed entity type.
     *
     * @param string $managedEntityType
     * @return Workflow
     */
    public function setManagedEntityType($managedEntityType)
    {
        $this->managedEntityType = $managedEntityType;
        return $this;
    }

    /**
     * Get managed entity type.
     *
     * @return string
     */
    public function getManagedEntityType()
    {
        return $this->managedEntityType;
    }

    /**
     * Set name.
     *
     * @param string $name
     * @return Workflow
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set start step.
     *
     * @param Step $startStep
     * @return Workflow
     */
    public function setStartStep($startStep)
    {
        $this->startStep = $startStep;
        return $this;
    }

    /**
     * Get startStep.
     *
     * @return Step
     */
    public function getStartStep()
    {
        return $this->startStep;
    }

    /**
     * Set steps.
     *
     * @param Step[] $steps
     * @return Workflow
     */
    public function setSteps($steps)
    {
        $data = array();
        foreach ($steps as $step) {
            $data[$step->getName()] = $step;
        }
        unset($steps);
        $this->steps = new ArrayCollection($data);
        return $this;
    }

    /**
     * Get steps.
     *
     * @return Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get step attributes.
     *
     * @return Collection
     * @TODO Cover method with tests
     */
    public function getStepAttributes()
    {
        $result = new ArrayCollection();
        /** @var $step Step */
        foreach ($this->steps as $step) {
            /** @var $attribute StepAttribute */
            foreach ($step->getAttributes() as $attribute) {
                $result->set($attribute->getName(), $attribute);
            }
        }

        return $result;
    }

    /**
     * Set transitions.
     *
     * @param Transition[] $transitions
     * @return Workflow
     */
    public function setTransitions($transitions)
    {
        $data = array();
        foreach ($transitions as $transition) {
            $data[$transition->getName()] = $transition;
        }
        unset($transitions);
        $this->transitions = new ArrayCollection($data);
        return $this;
    }

    /**
     * Get transitions.
     *
     * @return Collection
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Check if transition allowed for workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param string|Transition $transition
     * @return bool
     */
    public function isTransitionAllowed($workflowItem, $transition)
    {
        $this->assertTransitionArgument($transition);
        if (is_string($transition)) {
            if (!$this->transitions->containsKey($transition)) {
                return false;
            }
            $transition = $this->getTransitions()->get($transition);
        }

        return $transition->isAllowed($workflowItem);
    }

    /**
     * Transit workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param string|Transition $transition
     * @throws \Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException
     * @throws \Oro\Bundle\WorkflowBundle\Exception\UnknownStepException
     * @throws \Oro\Bundle\WorkflowBundle\Exception\UnknownTransitionException
     */
    public function transit(WorkflowItem $workflowItem, $transition)
    {
        $this->assertTransitionArgument($transition);
        if (is_string($transition)) {
            if (!$this->transitions->containsKey($transition)) {
                throw new UnknownTransitionException(sprintf('Unknown transition "%s".', $transition));
            }
            $transition = $this->getTransitions()->get($transition);
        }

        /** @var Step $currentStep */
        $currentStep = $this->getSteps()->get($workflowItem->getCurrentStepName());
        if (!$currentStep) {
            throw new UnknownStepException(
                sprintf('Unknown step "%s".', $workflowItem->getCurrentStepName())
            );
        }
        if ($currentStep->isAllowedTransition($transition->getName())) {
            $transition->transit($workflowItem);
        } else {
            throw new ForbiddenTransitionException(
                sprintf(
                    'Transition "%s" is not allowed for step "%s".',
                    $transition->getName(),
                    $currentStep->getName()
                )
            );
        }
    }

    /**
     * Create workflow item.
     *
     * @return WorkflowItem
     */
    public function createWorkflowItem()
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($this->getName());
        $workflowItem->setCurrentStepName($this->getStartStep()->getName());

        return $workflowItem;
    }

    /**
     * Check transition argument type.
     *
     * @param string|Transition $transition
     * @throws \InvalidArgumentException
     */
    protected function assertTransitionArgument($transition)
    {
        if (!is_string($transition) && !($transition instanceof Transition)) {
            throw new \InvalidArgumentException('Expected transition argument type is string or Transition');
        }
    }
}
