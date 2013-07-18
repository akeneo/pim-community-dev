<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\ConditionInterface;
use Oro\Bundle\WorkflowBundle\Model\PostActionInterface;
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
     * @var ConditionInterface
     */
    protected $condition;

    /**
     * @var PostActionInterface
     */
    protected $postAction;

    /**
     * Set condition.
     *
     * @param ConditionInterface $condition
     * @return Transition
     */
    public function setCondition(ConditionInterface $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Get condition.
     *
     * @return ConditionInterface
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
    public function setPostAction(PostActionInterface $postAction)
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

            $this->postAction->execute($workflowItem);
        }
    }
}
