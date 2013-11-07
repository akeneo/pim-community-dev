<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Step;

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
     * @var PostActionInterface|null
     */
    protected $postAction;

    /**
     * @var bool
     */
    protected $start = false;

    /**
     * @var array
     */
    protected $frontendOptions = array();

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
     * @param PostActionInterface $postAction
     * @return Transition
     */
    public function setPostAction(PostActionInterface $postAction = null)
    {
        $this->postAction = $postAction;
        return $this;
    }

    /**
     * Get post action.
     *
     * @return PostActionInterface
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
     * Check is transition allowed for current workflow item.
     *
     * @param WorkflowItem $workflowItem
     * @return boolean
     */
    public function isAllowed(WorkflowItem $workflowItem)
    {
        if (!$this->condition) {
            return true;
        }

        return $this->condition->isAllowed($workflowItem);
    }

    /**
     * Run transition process.
     *
     * @param WorkflowItem $workflowItem
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
     * @return Attribute
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
}
