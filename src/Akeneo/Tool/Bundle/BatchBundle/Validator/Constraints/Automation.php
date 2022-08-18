<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Automation extends Constraint
{
    public function validatedBy(): string
    {
        return 'akeneo_job_instance_automation_validator';
    }
}
