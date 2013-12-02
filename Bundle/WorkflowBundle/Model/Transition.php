<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;

class Transition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Step
     */
    protected $stepTo;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var ConditionInterface|null
     */
    protected $condition;

    /**
     * @var ConditionInterface|null
     */
    protected $preCondition;

    /**
     * @var ActionInterface|null
     */
    protected $postAction;

    /**
     * @var bool
     */
    protected $start = false;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var array
     */
    protected $frontendOptions = array();

    /**
     * @var string
     */
    protected $formType;

    /**
     * @var array
     */
    protected $formOptions = array();

    /**
     * @var string
     */
    protected $message;

    /**
     * @var bool
     */
    protected $unavailableHidden = false;

    /**
     * Set label.
     *
     * @param string $label
     * @return Transition
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
     * Set condition.
     *
     * @param ConditionInterface $condition
     * @return Transition
     */
    public function setCondition(ConditionInterface $condition = null)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Get condition.
     *
     * @return ConditionInterface|null
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set pre-condition.
     *
     * @param ConditionInterface|null $condition
     * @return Transition
     */
    public function setPreCondition($condition)
    {
        $this->preCondition = $condition;
        return $this;
    }

    /**
     * Get pre-condition.
     *
     * @return ConditionInterface|null
     */
    public function getPreCondition()
    {
        return $this->preCondition;
    }

    /**
     * Set name.
     *
     * @param string $name
     * @return Transition
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
     * Set post action.
     *
     * @param ActionInterface $postAction
     * @return Transition
     */
    public function setPostAction(ActionInterface $postAction = null)
    {
        $this->postAction = $postAction;
        return $this;
    }

    /**
     * Get post action.
     *
     * @return ActionInterface|null
     */
    public function getPostAction()
    {
        return $this->postAction;
    }

    /**
     * Set step to.
     *
     * @param Step $stepTo
     * @return Transition
     */
    public function setStepTo(Step $stepTo)
    {
        $this->stepTo = $stepTo;
        return $this;
    }

    /**
     * Get step to.
     *
     * @return Step
     */
    public function getStepTo()
    {
        return $this->stepTo;
    }

    /**
     * Check is transition condition is allowed for current workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param Collection|null $errors
     * @return boolean
     */
    protected function isConditionAllowed(WorkflowItem $workflowItem, Collection $errors = null)
    {
        if (!$this->condition) {
            return true;
        }

        return $this->condition->isAllowed($workflowItem, $errors);
    }

    /**
     * Check is transition pre condition is allowed for current workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param Collection|null $errors
     * @return boolean
     */
    protected function isPreConditionAllowed(WorkflowItem $workflowItem, Collection $errors = null)
    {
        if (!$this->preCondition) {
            return true;
        }

        return $this->preCondition->isAllowed($workflowItem, $errors);
    }

    /**
     * Check is transition allowed for current workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @param Collection $errors
     * @return bool
     */
    public function isAllowed(WorkflowItem $workflowItem, Collection $errors = null)
    {
        return $this->isPreConditionAllowed($workflowItem, $errors)
            && $this->isConditionAllowed($workflowItem, $errors);
    }

    /**
     * Check that transition is available to show.
     *
     * @param WorkflowItem $workflowItem
     * @param Collection $errors
     * @return bool
     */
    public function isAvailable(WorkflowItem $workflowItem, Collection $errors = null)
    {
        if ($this->hasForm()) {
            return $this->isPreConditionAllowed($workflowItem, $errors);
        } else {
            return $this->isAllowed($workflowItem, $errors);
        }
    }

    /**
     * Run transition process.
     *
     * @param WorkflowItem $workflowItem
     * @throws ForbiddenTransitionException
     */
    public function transit(WorkflowItem $workflowItem)
    {
        if ($this->isAllowed($workflowItem)) {
            $stepTo = $this->getStepTo();
            $workflowItem->setCurrentStepName($stepTo->getName());
            if ($stepTo->isFinal() || !$stepTo->hasAllowedTransitions()) {
                $workflowItem->setClosed(true);
            }

            if ($this->postAction) {
                $this->postAction->execute($workflowItem);
            }
        } else {
            throw new ForbiddenTransitionException(
                sprintf('Transition "%s" is not allowed.', $this->getName())
            );
        }
    }

    /**
     * Mark transition as start transition
     *
     * @param boolean $start
     * @return Transition
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStart()
    {
        return $this->start;
    }

    /**
     * Set frontend options.
     *
     * @param array $frontendOptions
     * @return Transition
     */
    public function setFrontendOptions(array $frontendOptions)
    {
        $this->frontendOptions = $frontendOptions;
        return $this;
    }

    /**
     * Get frontend options.
     *
     * @return array
     */
    public function getFrontendOptions()
    {
        return $this->frontendOptions;
    }

    /**
     * @return bool
     */
    public function hasForm()
    {
        return !empty($this->formOptions);
    }

    /**
     * @param string $formType
     * @return Transition
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @param array $formOptions
     * @return Transition
     */
    public function setFormOptions(array $formOptions)
    {
        $this->formOptions = $formOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getFormOptions()
    {
        return $this->formOptions;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param boolean $hidden
     * @return Transition
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Transition
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isUnavailableHidden()
    {
        return $this->unavailableHidden;
    }

    /**
     * @param boolean $unavailableHidden
     * @return Transition
     */
    public function setUnavailableHidden($unavailableHidden)
    {
        $this->unavailableHidden = $unavailableHidden;
        return $this;
    }
}
