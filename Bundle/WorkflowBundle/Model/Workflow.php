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
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;

class Workflow
{
    const MANAGED_ENTITY_KEY = 'managed_entity';

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
    protected $attributes;

    /**
     * @var Collection
     */
    protected $transitions;

    /**
     * @var string
     */
    protected $startStepName;

    /**
     * @var string
     */
    protected $managedEntityClass;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var EntityBinder
     */
    protected $entityBinder;

    /**
     * @param EntityBinder $entityBinder
     */
    public function __construct(EntityBinder $entityBinder)
    {
        $this->entityBinder = $entityBinder;

        $this->transitions = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->attributes = new ArrayCollection();
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
    public function setManagedEntityClass($managedEntityType)
    {
        $this->managedEntityClass = $managedEntityType;
        return $this;
    }

    /**
     * Get managed entity type.
     *
     * @return string
     */
    public function getManagedEntityClass()
    {
        return $this->managedEntityClass;
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
     * Get start step
     *
     * @return Step
     */
    public function getStartStep()
    {
        return $this->getStep($this->getStartStepName());
    }

    /**
     * Get step by name
     *
     * @param string $stepName
     * @return Step
     * @throws UnknownStepException If step is not found
     */
    public function getStep($stepName)
    {
        $result = $this->getSteps()->get($stepName);
        if (!$result) {
            throw new UnknownStepException($stepName);
        }
        return $result;
    }

    /**
     * Set start step.
     *
     * @param string $startStep
     * @return Workflow
     */
    public function setStartStepName($startStep)
    {
        $this->startStepName = $startStep;
        return $this;
    }

    /**
     * Get start step name
     *
     * @return string
     */
    public function getStartStepName()
    {
        return $this->startStepName;
    }

    /**
     * Set steps.
     *
     * @param Step[]|Collection $steps
     * @return Workflow
     */
    public function setSteps($steps)
    {
        if ($steps instanceof Collection) {
            $this->steps = $steps;
        } else {
            $data = array();
            foreach ($steps as $step) {
                $data[$step->getName()] = $step;
            }
            unset($steps);
            $this->steps = new ArrayCollection($data);
        }

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
     * Set attributes.
     *
     * @param Attribute[]|Collection $attributes
     * @return Workflow
     */
    public function setAttributes($attributes)
    {
        if ($attributes instanceof Collection) {
            $this->attributes = $attributes;
        } else {
            $data = array();
            foreach ($attributes as $attribute) {
                $data[$attribute->getName()] = $attribute;
            }
            unset($attributes);
            $this->attributes = new ArrayCollection($data);
        }

        return $this;
    }

    /**
     * Get step attributes.
     *
     * @return Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set transitions.
     *
     * @param Transition[]|Collection $transitions
     * @return Workflow
     */
    public function setTransitions($transitions)
    {
        if ($transitions instanceof Collection) {
            $this->transitions = $transitions;
        } else {
            $data = array();
            foreach ($transitions as $transition) {
                $data[$transition->getName()] = $transition;
            }
            unset($transitions);
            $this->transitions = new ArrayCollection($data);
        }

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
            throw new UnknownStepException($workflowItem->getCurrentStepName());
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
     * @param object|null $entity
     * @return WorkflowItem
     * @throws \LogicException
     */
    public function createWorkflowItem($entity = null)
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($this->getName());
        $workflowItem->setCurrentStepName($this->getStartStepName());

        // set managed entity
        if ($this->managedEntityClass) {
            if (!$entity) {
                throw new \LogicException('Managed entity must exist');
            } elseif (!($entity instanceof $this->managedEntityClass)) {
                throw new \LogicException(sprintf('Managed entity must be instance of %s', $this->managedEntityClass));
            }

            $this->entityBinder->bind($workflowItem, $entity);
            $workflowItem->getData()->set(self::MANAGED_ENTITY_KEY, $entity);
        }

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

    /**
     * Set label.
     *
     * @param string $label
     * @return Workflow
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
