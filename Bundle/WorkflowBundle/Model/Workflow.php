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
    const MANAGED_ENTITY_KEY = 'managed_entity';
    const DEFAULT_START_TRANSITION_NAME = '__start__';

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
    protected $managedEntityClass;

    /**
     * @var string
     */
    protected $label;

    public function __construct()
    {
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
     * Get attribute by name
     *
     * @param string $name
     * @return Attribute|null
     */
    public function getAttribute($name)
    {
        return $this->attributes->get($name);
    }

    /**
     * Get attributes with option "managed_entity"
     *
     * @return Collection
     */
    public function getManagedEntityAttributes()
    {
        return $this->getAttributes()->filter(
            function (Attribute $attribute) {
                return $attribute->getType() == 'entity' && $attribute->getOption('managed_entity');
            }
        );
    }

    /**
     * Get list of attributes that require binding
     *
     * @return Collection
     */
    public function getBindEntityAttributes()
    {
        return $this->getAttributes()->filter(
            function (Attribute $attribute) {
                return $attribute->getType('entity') && $attribute->getOption('bind');
            }
        );
    }

    /**
     * Get list of attributes names that require binding
     *
     * @return array
     */
    public function getBindEntityAttributeNames()
    {
        $result = array();

        /** @var Attribute $attribute  */
        foreach ($this->getBindEntityAttributes() as $attribute) {
            $result[] = $attribute->getName();
        }

        return $result;
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
     * Start workflow.
     *
     * @param array $data
     * @param string $startTransitionName
     * @return WorkflowItem
     */
    public function start(array $data = array(), $startTransitionName = self::DEFAULT_START_TRANSITION_NAME)
    {
        $workflowItem = $this->createWorkflowItem($data);
        $this->transit($workflowItem, $startTransitionName);
        return $workflowItem;
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

        if ($this->isAllowedTransition($transition, $workflowItem)) {
            $transition->transit($workflowItem);
        } else {
            throw new ForbiddenTransitionException(
                sprintf(
                    'Transition "%s" is not allowed.',
                    $transition->getName()
                )
            );
        }
    }

    /**
     * Check that transition is allowed to perform for given workflow item.
     *
     * @param Transition $transition
     * @param WorkflowItem $workflowItem
     * @return bool
     * @throws UnknownStepException
     */
    protected function isAllowedTransition(Transition $transition, WorkflowItem $workflowItem)
    {
        /** @var Step $currentStep */
        $currentStep = $this->getSteps()->get($workflowItem->getCurrentStepName());
        if (!$currentStep && !$transition->isStart()) {
            throw new UnknownStepException($workflowItem->getCurrentStepName());
        }
        return (!$currentStep && $transition->isStart()) || $currentStep->isAllowedTransition($transition->getName());
    }

    /**
     * Create workflow item.
     *
     * @param array $data
     * @return WorkflowItem
     * @throws \LogicException
     */
    public function createWorkflowItem(array $data = array())
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($this->getName());
        $workflowItem->getData()->add($data);

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

    /**
     * Get allowed start transitions.
     *
     * @param array $data
     * @return Collection
     */
    public function getAllowedStartTransitions(array $data = array())
    {
        $workflowItem = $this->createWorkflowItem($data);
        return $this->getTransitions()->filter(
            function (Transition $transition) use ($workflowItem) {
                return $transition->isStart() && $transition->isAllowed($workflowItem);
            }
        );
    }
}
