<?php

namespace Pim\Bundle\Batch2Bundle\Model;

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
}
