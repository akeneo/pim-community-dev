<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowTransitionRecord;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\UnknownStepException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;

class Workflow
{
    const DEFAULT_START_TRANSITION_NAME = '__start__';
    const TYPE_ENTITY = 'entity';
    const TYPE_WIZARD = 'wizard';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var StepManager
     */
    protected $stepManager;

    /**
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * @var TransitionManager
     */
    protected $transitionManager;

    /**
     * @var EntityBinder
     */
    protected $entityBinder;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Collection
     */
    protected $errors;

    /**
     * @param StepManager|null $stepManager
     * @param AttributeManager|null $attributeManager
     * @param TransitionManager|null $transitionManager
     */
    public function __construct(
        StepManager $stepManager = null,
        AttributeManager $attributeManager = null,
        TransitionManager $transitionManager = null
    ) {
        $this->stepManager = $stepManager ? $stepManager : new StepManager();
        $this->attributeManager  = $attributeManager ? $attributeManager : new AttributeManager();
        $this->transitionManager = $transitionManager ? $transitionManager : new TransitionManager();
    }

    /**
     * @param EntityBinder $entityBinder
     */
    public function setEntityBinder(EntityBinder $entityBinder)
    {
        $this->entityBinder = $entityBinder;
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
     * Set type.
     *
     * @param string $type
     * @return Workflow
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return StepManager
     */
    public function getStepManager()
    {
        return $this->stepManager;
    }

    /**
     * @return AttributeManager
     */
    public function getAttributeManager()
    {
        return $this->attributeManager;
    }

    /**
     * @return TransitionManager
     */
    public function getTransitionManager()
    {
        return $this->transitionManager;
    }

    /**
     * Start workflow.
     *
     * @param array $data
     * @param string $startTransitionName
     * @return WorkflowItem
     */
    public function start(array $data = array(), $startTransitionName = null)
    {
        if (null === $startTransitionName) {
            $startTransitionName = self::DEFAULT_START_TRANSITION_NAME;
        }

        $workflowItem = $this->createWorkflowItem($data);
        $this->transit($workflowItem, $startTransitionName);

        return $workflowItem;
    }

    /**
     * Check if transition allowed for workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param string|Transition $transition
     * @param Collection $errors
     * @param bool $fireExceptions
     * @return bool
     * @throws InvalidTransitionException
     */
    public function isTransitionAllowed(
        WorkflowItem $workflowItem,
        $transition,
        Collection $errors = null,
        $fireExceptions = false
    ) {
        // get current transition
        try {
            $transition = $this->transitionManager->extractTransition($transition);
        } catch (InvalidTransitionException $e) {
            if ($fireExceptions) {
                throw $e;
            } else {
                return false;
            }
        }

        $transitionIsValid = $this->checkTransitionValid($transition, $workflowItem, $fireExceptions);

        return $transitionIsValid && $transition->isAllowed($workflowItem, $errors);
    }

    /**
     * Checks whether transition is valid in context of workflow item state.
     *
     * Transition is considered invalid when workflow item is new and transition is not "start".
     * Also transition is considered invalid when current step doesn't contain such allowed transition.
     *
     * @param Transition $transition
     * @param WorkflowItem $workflowItem
     * @param bool $fireExceptions
     * @return bool
     * @throws InvalidTransitionException
     */
    protected function checkTransitionValid(Transition $transition, WorkflowItem $workflowItem, $fireExceptions)
    {
        // get current step
        $currentStep = null;
        $currentStepName = $workflowItem->getCurrentStepName();
        if ($currentStepName) {
            $currentStep = $this->stepManager->getStep($currentStepName);
        }

        // if there is no current step - consider transition as a start transition
        if (!$currentStep) {
            if (!$transition->isStart()) {
                if ($fireExceptions) {
                    throw InvalidTransitionException::notStartTransition(
                        $workflowItem->getWorkflowName(),
                        $transition->getName()
                    );
                }
                return false;
            }
        } elseif (!$currentStep->isAllowedTransition($transition->getName())) {
            // if transition is not allowed for current step
            if ($fireExceptions) {
                throw InvalidTransitionException::stepHasNoAllowedTransition(
                    $workflowItem->getWorkflowName(),
                    $currentStep->getName(),
                    $transition->getName()
                );
            }
            return false;
        }

        return true;
    }

    /**
     * Transit workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param string|Transition $transition
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     */
    public function transit(WorkflowItem $workflowItem, $transition)
    {
        $transition = $this->transitionManager->extractTransition($transition);

        $this->checkTransitionValid($transition, $workflowItem, true);

        $transitionRecord = $this->createTransitionRecord($workflowItem, $transition);
        $transition->transit($workflowItem);
        $workflowItem->addTransitionRecord($transitionRecord);
        $this->bindEntities($workflowItem);
    }

    /**
     * Bind entities to workflow item
     *
     * @param WorkflowItem $workflowItem
     * @return bool
     * @throws \LogicException
     */
    public function bindEntities(WorkflowItem $workflowItem)
    {
        if (!$this->entityBinder) {
            throw new \LogicException('Entity binder is not set.');
        }
        return $this->entityBinder->bindEntities($workflowItem);
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
     * @param WorkflowItem $workflowItem
     * @param Transition $transition
     * @return WorkflowTransitionRecord
     */
    protected function createTransitionRecord(WorkflowItem $workflowItem, Transition $transition)
    {
        $transitionName = $transition->getName();
        $stepFrom = $workflowItem->getCurrentStepName();
        $stepTo = $transition->getStepTo()->getName();

        $transitionRecord = new WorkflowTransitionRecord();
        $transitionRecord
            ->setTransitionName($transitionName)
            ->setStepFromName($stepFrom)
            ->setStepToName($stepTo);

        return $transitionRecord;
    }

    /**
     * Check that start transition is available to show.
     *
     * @param string|Transition $transition
     * @param array $data
     * @param Collection $errors
     * @return bool
     */
    public function isStartTransitionAvailable($transition, array $data = array(), Collection $errors = null)
    {
        $workflowItem = $this->createWorkflowItem($data);

        return $this->isTransitionAvailable($workflowItem, $transition, $errors);
    }

    /**
     * Check that transition is available to show.
     *
     * @param string|Transition $transition
     * @param WorkflowItem $workflowItem
     * @param Collection $errors
     * @return bool
     */
    public function isTransitionAvailable(WorkflowItem $workflowItem, $transition, Collection $errors = null)
    {
        $transition = $this->transitionManager->extractTransition($transition);

        return $transition->isAvailable($workflowItem, $errors);
    }

    /**
     * Get transitions for existing workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @return Collection|Transition[]
     * @throws UnknownStepException
     */
    public function getTransitionsByWorkflowItem(WorkflowItem $workflowItem)
    {
        $currentStepName = $workflowItem->getCurrentStepName();
        $currentStep = $this->stepManager->getStep($currentStepName);
        if (!$currentStep) {
            throw new UnknownStepException($currentStepName);
        }

        $transitions = new ArrayCollection();
        $transitionNames = $currentStep->getAllowedTransitions();
        foreach ($transitionNames as $transitionName) {
            $transition = $this->transitionManager->extractTransition($transitionName);
            $transitions->add($transition);
        }

        return $transitions;
    }
}
