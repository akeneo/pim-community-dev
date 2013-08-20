<?php

namespace Pim\Bundle\BatchBundle\Model;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Job
{
    protected $code;

    protected $steps;

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setSteps(array $steps)
    {
        $this->steps = $steps;

        return $this;
    }

    public function addStep($step)
    {
        $this->steps[] = $step;

        return $this;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Retrieve the step with the given name. If there is no Step with the given
     * name, then return null.
     *
     * @param string $stepTitle
     *
     * @return null|Step the Step
     */
    public function getStep($stepTitle)
    {
        foreach ($this->steps as $step) {
            if ($step->getName() === $stepTitle) {
                return $step;
            }
        }
    }

    /**
     * Get the steps configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = array();
        foreach ($this->steps as $step) {
            $result[$step->getName()] = $step->getConfiguration();
        }

        return $result;
    }

    /**
     * Set the steps configuration
     *
     * @param array $steps
     */
    public function setConfiguration(array $steps)
    {
        foreach ($steps as $title => $config) {
            $step = $this->getStep($title);
            if (!$step) {
                throw new \InvalidArgumentException(sprintf('Unknown step "%s"', $title));
            }

            $step->setConfiguration($config);
        }
    }
}
