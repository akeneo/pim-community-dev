<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Step\StepInterface;

interface JobWithStepsInterface
{
    /**
     * @return StepInterface[]
     */
    public function getSteps(): array;
}
