<?php

namespace Oro\Bundle\WorkflowBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class StepManager
{
    /**
     * @var Collection
     */
    protected $steps;

    /**
     * @param Collection $steps
     */
    public function __construct(Collection $steps = null)
    {
        $this->steps = $steps ?: new ArrayCollection();
    }

    /**
     * Get step by name
     *
     * @param string $stepName
     * @return Step
     */
    public function getStep($stepName)
    {
        return $this->steps->get($stepName);
    }

    /**
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
     * @return Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Get steps sorted by order.
     *
     * @return Collection|Step[]
     */
    public function getOrderedSteps()
    {
        $steps = $this->steps->toArray();
        usort(
            $steps,
            function (Step $stepOne, Step $stepTwo) {
                return ($stepOne->getOrder() >= $stepTwo->getOrder()) ? 1 : -1;
            }
        );
        return new ArrayCollection($steps);
    }
}
