<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class TransitionManager
{
    /**
     * @var Collection
     */
    protected $transitions;

    /**
     * @param Collection $transitions
     */
    public function __construct(Collection $transitions = null)
    {
        $this->transitions = $transitions ?: new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * @param string $transitionName
     * @return Transition|null
     */
    public function getTransition($transitionName)
    {
        return $this->transitions->get($transitionName);
    }

    /**
     * @param Transition[]|Collection $transitions
     * @return TransitionManager
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
     * Check transition argument type.
     *
     * @param string|Transition $transition
     * @throws \InvalidArgumentException
     */
    protected function assertTransitionArgument($transition)
    {
        if (!is_string($transition) && !$transition instanceof Transition) {
            throw new \InvalidArgumentException('Expected transition argument type is string or Transition');
        }
    }

    /**
     * Receive transition by name or object
     *
     * @param string|Transition $transition
     * @return null|Transition
     */
    public function extractTransition($transition)
    {
        $this->assertTransitionArgument($transition);
        if (is_string($transition)) {
            $transitionName = $transition;
            $transition = $this->getTransition($transitionName);
        }

        return $transition;
    }

    /**
     * Get allowed start transitions
     *
     * @param WorkflowItem $workflowItem
     * @return Collection
     */
    public function getAllowedStartTransitions(WorkflowItem $workflowItem)
    {
        return $this->getTransitions()->filter(
            function (Transition $transition) use ($workflowItem) {
                return $transition->isStart() && $transition->isAllowed($workflowItem);
            }
        );
    }
}
