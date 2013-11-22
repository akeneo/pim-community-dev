<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;

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
     * @return Transition
     * @throws InvalidTransitionException
     */
    public function getTransition($transitionName)
    {
        $result = $this->transitions->get($transitionName);
        if (!$result) {
            throw InvalidTransitionException::unknownTransition($transitionName);
        }
        return $result;
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected transition argument type is string or Transition, but %s given',
                    is_object($transition) ? get_class($transition) : gettype($transition)
                )
            );
        }
    }

    /**
     * Receive transition by name or object
     *
     * @param string|Transition $transition
     * @return Transition
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
     * Get start transitions
     *
     * @return Collection
     */
    public function getStartTransitions()
    {
        return $this->getTransitions()->filter(
            function (Transition $transition) {
                return $transition->isStart();
            }
        );
    }
}
